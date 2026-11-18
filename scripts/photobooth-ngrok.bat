@echo off
setlocal
cd /d "%~dp0.."

start "" /b wscript.exe "%~dp0start-server.vbs"

set /a TRY=0
:wait
set /a TRY+=1
set "DB=0"
set "SRV=0"
netstat -ano -p tcp | findstr /c:":3306" | findstr /c:"LISTENING" >nul 2>&1
if not errorlevel 1 set "DB=1"
netstat -ano -p tcp | findstr /c:":8000" | findstr /c:"LISTENING" >nul 2>&1
if not errorlevel 1 set "SRV=1"
if "%DB%"=="1" if "%SRV%"=="1" goto ready
if %TRY% GEQ 20 goto ready
ping -n 1 127.0.0.1 >nul
goto wait
:ready

set "NGROK_URL="
for /f "usebackq tokens=1,* delims==" %%A in (`findstr /b "APP_URL=" ".env"`) do set "NGROK_URL=%%B"
if not defined NGROK_URL set "NGROK_URL=https://metalinguistic-unvenerably-fernande.ngrok-free.dev"

set "EDGE=%ProgramFiles(x86)%\Microsoft\Edge\Application\msedge.exe"
if not exist "%EDGE%" set "EDGE=%ProgramFiles%\Microsoft\Edge\Application\msedge.exe"
if not exist "%EDGE%" set "EDGE=msedge"

set "PROFILE=%LOCALAPPDATA%\Photobooth\edge-testing"
start "" "%EDGE%" --app="%NGROK_URL%" --window-size=1366,768 --user-data-dir="%PROFILE%" --no-first-run --no-default-browser-check
endlocal
