::@echo off

cd ../../../
cd script

::Clear subject
start /min BotClear.bat 2 5 17 18 19 20 22 23 24 25 27 28 29 30 31 32 33 34 35 36
timeout /t 5

cd ../../../
cd script

echo Start Match ....

echo ====== Start Warroom Match-Auto ====== %date% %time% >> C:\script\log_match\Match_Auto.txt

start /min MatchAuto.bat 2 5 17 18 19 20 22 23 24 25 27 28 29 30 31 32 33 34 35 36
timeout /t 3

:loop 
FOR /F "tokens=*" %%A IN ('tasklist /FI "WINDOWTITLE eq Administrator:  MatchAuto*" ^| find "cmd.exe" /c') DO SET returnvalue=%%A
echo Bot Running : %returnvalue%
if %returnvalue% GTR 0 (
	timeout /T 120 /NOBREAK
	goto loop
)	

echo End Warroom Match-Auto %date% %time% >> C:\script\log_match\Match_Auto.txt

timeout /t 5

cd ../../../
cd script

Index_Match_Auto.bat