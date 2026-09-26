@echo off
setlocal
cd /d "%~dp0"
echo.
echo mpGESTAO - servidor local
echo.
set "MP_PHP="
for /f "delims=" %%P in ('where php 2^>nul') do if not defined MP_PHP set "MP_PHP=%%P"
if not defined MP_PHP if exist "C:\xampp\php\php.exe" set "MP_PHP=C:\xampp\php\php.exe"
if not defined MP_PHP if exist "D:\xampp\php\php.exe" set "MP_PHP=D:\xampp\php\php.exe"
if defined MP_PHP goto php
where docker >nul 2>&1
if errorlevel 1 goto missing
echo Iniciando Docker Compose. Mantenha o Docker Desktop aberto.
docker compose up --build -d
if errorlevel 1 goto failed
goto open
:php
"%MP_PHP%" scripts\check-runtime.php
if errorlevel 1 goto failed
for %%P in ("%MP_PHP%") do set "MP_PHP_DIR=%%~dpP"
if exist config\local.php goto database
if defined DB_HOST goto database
if defined DB_PORT goto database
if not exist "%MP_PHP_DIR%..\mysql_start.bat" goto database
powershell -NoProfile -Command "$c=New-Object Net.Sockets.TcpClient; try{$t=$c.ConnectAsync('127.0.0.1',3306); if($t.Wait(600) -and $c.Connected){exit 0}else{exit 1}}catch{exit 1}finally{$c.Dispose()}"
if not errorlevel 1 goto database
echo Iniciando MySQL do XAMPP...
start "MySQL - mpGESTAO" /D "%MP_PHP_DIR%.." "%MP_PHP_DIR%..\mysql_start.bat"
powershell -NoProfile -Command "for($i=0;$i -lt 30;$i++){ $c=New-Object Net.Sockets.TcpClient; try{$t=$c.ConnectAsync('127.0.0.1',3306); if($t.Wait(500) -and $c.Connected){exit 0}}catch{}finally{$c.Dispose()}; Start-Sleep -Seconds 1 }; exit 1"
if errorlevel 1 goto failed
:database
echo Conferindo banco e tabelas...
"%MP_PHP%" bin\install.php
if errorlevel 1 goto failed
powershell -NoProfile -Command "$c=New-Object Net.Sockets.TcpClient; try{$t=$c.ConnectAsync('127.0.0.1',8000); if($t.Wait(600) -and $c.Connected){exit 1}else{exit 0}}catch{exit 0}finally{$c.Dispose()}"
if errorlevel 1 goto occupied
start "mpGESTAO - mantenha esta janela aberta" "%MP_PHP%" -S 127.0.0.1:8000 -t public
:open
powershell -NoProfile -Command "for($i=0;$i -lt 60;$i++){try{$r=Invoke-WebRequest 'http://127.0.0.1:8000/?page=login' -UseBasicParsing -TimeoutSec 2; if($r.StatusCode -eq 200 -and $r.Content.Contains('mpGEST')){Start-Process 'http://127.0.0.1:8000'; exit 0}}catch{}; Start-Sleep -Seconds 1}; exit 1"
if errorlevel 1 goto failed
echo.
echo Projeto aberto em http://127.0.0.1:8000
if defined MP_PHP echo Feche a janela do servidor PHP para encerrar.
if not defined MP_PHP echo Para encerrar, execute: docker compose down
pause
exit /b 0
:occupied
echo A porta 8000 esta ocupada. Feche o servidor anterior e tente novamente.
pause
exit /b 1
:missing
echo PHP e Docker nao encontrados.
echo Instale XAMPP com PHP 8.2 ou superior ou Docker Desktop e tente novamente.
echo As instrucoes de instalacao estao no README.md.
pause
exit /b 1
:failed
echo.
echo Nao foi possivel iniciar. Confira a mensagem acima.
echo Verifique se o MySQL ou Docker Desktop esta ativo.
echo Para banco personalizado, configure config\local.php conforme o README.md.
pause
exit /b 1
