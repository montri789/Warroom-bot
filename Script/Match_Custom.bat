::@echo off

cd ../../../
cd script

::Clear subject matching_status='matching' and bot_id !=0
start /min BotClear.bat 39
timeout /t 5

cd ../../../
cd script

echo Start Match ....

echo ====== Start Warroom Match ====== %date% %time% >> C:\script\log_match\Match_Custom.txt

start /min MatchCustom.bat 39
timeout /t 5

:loop 
FOR /F "tokens=*" %%A IN ('tasklist /FI "WINDOWTITLE eq Administrator:  MatchCustom*" ^| find "cmd.exe" /c') DO SET returnvalue=%%A
echo Bot Running : %returnvalue%
if %returnvalue% GTR 0 (
	::timeout /T 120 /NOBREAK
	timeout /T 30 /NOBREAK
	goto loop
)	

echo End Warroom Match %date% %time% >> C:\script\log_match\Match_Custom.txt

timeout /t 5

cd ../../../
cd script

Index_Match_Custom.bat