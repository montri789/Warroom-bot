@echo off

cd ../../../
cd C:\Dropbox\warroom_bot

title MatchAuto 

@echo off
FOR %%A IN (%*) DO (
	echo running matcher_auto %%A
	php index.php matcher_custom bot_auto 9314 %%A
)

::pause
exit