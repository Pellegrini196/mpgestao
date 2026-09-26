<?php
declare(strict_types=1);
namespace App;
use PDO;
final class Repository {
    public function __construct(private PDO $db, private Usuario $user) {}
    private function table(string $type): string {
        if (!in_array($type,['fornecedores','produtos','cestas'],true)) throw new \InvalidArgumentException('Tipo inválido.');
        return $type;
    }
    public function all(string $type): array {
        $table=$this->table($type);
        $q=$this->db->prepare("SELECT * FROM $table WHERE usuario_id=? ORDER BY id DESC");
        $q->execute([$this->user->id]); return $q->fetchAll();
    }
    public function get(string $type,int $id): array {
        $table=$this->table($type);
        $q=$this->db->prepare("SELECT * FROM $table WHERE id=? AND usuario_id=?");
        $q->execute([$id,$this->user->id]);
        return $q->fetch() ?: throw new \RuntimeException('Registro não encontrado.',404);
    }
    public function save(string $type,array $data,?int $id=null): int {
        $table=$this->table($type);
        if ($id!==null) $this->get($type,$id);
        $fields=['nome'=>Validation::text($data,'nome',100)];
        if ($type==='fornecedores') {
            $fields['email']=Validation::email($data);
            $fields['telefone']=Validation::text($data,'telefone',30,false);
        }
        if ($type==='produtos') {
            $fields['fornecedor_id']=Validation::id($data['fornecedor_id'] ?? null);
            $this->get('fornecedores',$fields['fornecedor_id']);
            $fields['descricao']=Validation::text($data,'descricao',500,false);
            $cents=Validation::cents($data['preco'] ?? '');
            $fields['preco']=sprintf('%d.%02d',intdiv($cents,100),$cents%100);
        }
        if ($id===null) {
            $fields['usuario_id']=$this->user->id;
            $columns=implode(',',array_keys($fields));
            $marks=implode(',',array_fill(0,count($fields),'?'));
            $this->db->prepare("INSERT INTO $table ($columns) VALUES ($marks)")->execute(array_values($fields));
            return (int)$this->db->lastInsertId();
        }
        $set=implode(',',array_map(fn($key)=>"$key=?",array_keys($fields)));
        $this->db->prepare("UPDATE $table SET $set WHERE id=? AND usuario_id=?")->execute([...array_values($fields),$id,$this->user->id]);
        return $id;
    }
    public function basket(int $id): Cesta {
        $row=$this->get('cestas',$id); $basket=new Cesta($id,$row['nome'],$this->user);
        $q=$this->db->prepare('SELECT p.*, f.nome fornecedor_nome FROM cesta_produtos cp JOIN produtos p ON p.id=cp.produto_id JOIN fornecedores f ON f.id=p.fornecedor_id WHERE cp.cesta_id=? AND cp.usuario_id=? ORDER BY p.nome');
        $q->execute([$id,$this->user->id]);
        foreach ($q as $p) $basket->adicionar(new Produto((int)$p['id'],$p['nome'],Validation::cents($p['preco']),new Fornecedor((int)$p['fornecedor_id'],$p['fornecedor_nome'])));
        return $basket;
    }
    public function add(int $basketId,mixed $ids): void {
        if (!is_array($ids)||count($ids)<1||count($ids)>500) throw new \InvalidArgumentException('Selecione de 1 a 500 produtos.');
        $ids=array_unique(array_map(fn($id)=>Validation::id($id),$ids));
        $this->db->beginTransaction();
        try {
            $this->get('cestas',$basketId);
            foreach ($ids as $id) $this->get('produtos',$id);
            $q=$this->db->prepare('INSERT INTO cesta_produtos(cesta_id,produto_id,usuario_id) VALUES(?,?,?) ON DUPLICATE KEY UPDATE produto_id=VALUES(produto_id)');
            foreach ($ids as $id) $q->execute([$basketId,$id,$this->user->id]);
            $this->db->commit();
        } catch (\Throwable $e) { $this->db->rollBack(); throw $e; }
    }
    public function remove(int $basketId,int $productId): void {
        $this->get('cestas',$basketId);
        $this->db->prepare('DELETE FROM cesta_produtos WHERE cesta_id=? AND produto_id=? AND usuario_id=?')->execute([$basketId,$productId,$this->user->id]);
    }
}
