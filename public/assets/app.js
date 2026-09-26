"use strict";
for (const form of document.querySelectorAll('.ajax-edit')) {
  form.addEventListener('submit', async event => {
    event.preventDefault();
    const button = form.querySelector('button');
    const status = form.querySelector('.form-status');
    button.disabled = true; status.textContent = 'Salvando…'; status.className = 'form-status mt-2';
    try {
      const payload = Object.fromEntries(new FormData(form));
      const response = await fetch('api.php', {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-Token':document.querySelector('meta[name="csrf-token"]').content}, body:JSON.stringify(payload)});
      const result = await response.json();
      if (!response.ok || !result.ok) throw new Error(result.message || 'Não foi possível salvar.');
      status.textContent = result.message; status.classList.add('text-success');
      if(payload.tipo === 'fornecedores') {
        for(const option of document.querySelectorAll('select[name="fornecedor_id"] option')) {
          if(option.value === payload.id) option.textContent = payload.nome.trim();
        }
      }
    } catch(error) {status.textContent = error instanceof TypeError ? 'Falha de conexão. Tente novamente.' : error.message; status.classList.add('text-danger');}
    finally {button.disabled = false;}
  });
}
const catalog = document.querySelector('#catalog-form');
if (catalog) {
  const count = () => catalog.querySelectorAll('.product-check:checked').length;
  const update = () => {
    const selected = count();
    document.querySelector('#selected-count').textContent = selected + (selected === 1 ? ' produto selecionado' : ' produtos selecionados');
    document.querySelector('#add-button').disabled = selected === 0 || selected > 500;
  };
  catalog.addEventListener('change', update);
  catalog.addEventListener('submit', event => {if(count() < 1 || count() > 500) {event.preventDefault(); alert('Selecione de 1 a 500 produtos.');}});
  update();
}
