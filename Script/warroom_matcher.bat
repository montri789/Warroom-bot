cd ../../../
cd Dropbox/warroom_bot

@ECHO OFF
@echo Clear Warroom Matcher (-1) %%A
"c:\Program Files (x86)\PHP\php.exe" -f ./index.php matcher clear %%A


@ECHO OFF
@echo Run Warroom Matcher
echo ====== start Run Warroom Matcher %date% %time% >> C:\Script\log_sphinx_reindex\warroom_match.txt

"c:\Program Files (x86)\PHP\php.exe" -f ./index.php matcher bot_auto 19
::"c:\Program Files (x86)\PHP\php.exe" -f ./index.php matcher bot_auto 1
"c:\Program Files (x86)\PHP\php.exe" -f ./index.php matcher bot_auto 2
"c:\Program Files (x86)\PHP\php.exe" -f ./index.php matcher bot_auto 5
"c:\Program Files (x86)\PHP\php.exe" -f ./index.php matcher bot_auto 17
"c:\Program Files (x86)\PHP\php.exe" -f ./index.php matcher bot_auto 18
"c:\Program Files (x86)\PHP\php.exe" -f ./index.php matcher bot_auto 20
"c:\Program Files (x86)\PHP\php.exe" -f ./index.php matcher bot_auto 22
"c:\Program Files (x86)\PHP\php.exe" -f ./index.php matcher bot_auto 23
"c:\Program Files (x86)\PHP\php.exe" -f ./index.php matcher bot_auto 24
"c:\Program Files (x86)\PHP\php.exe" -f ./index.php matcher bot_auto 25
"c:\Program Files (x86)\PHP\php.exe" -f ./index.php matcher bot_auto 27
"c:\Program Files (x86)\PHP\php.exe" -f ./index.php matcher bot_auto 28
"c:\Program Files (x86)\PHP\php.exe" -f ./index.php matcher bot_auto 29
"c:\Program Files (x86)\PHP\php.exe" -f ./index.php matcher bot_auto 30
"c:\Program Files (x86)\PHP\php.exe" -f ./index.php matcher bot_auto 31
"c:\Program Files (x86)\PHP\php.exe" -f ./index.php matcher bot_auto 32
"c:\Program Files (x86)\PHP\php.exe" -f ./index.php matcher bot_auto 33
"c:\Program Files (x86)\PHP\php.exe" -f ./index.php matcher bot_auto 34
"c:\Program Files (x86)\PHP\php.exe" -f ./index.php matcher bot_auto 35

echo End Run Warroom Matcher %date% %time% >> C:\Script\log_sphinx_reindex\warroom_match.txt
