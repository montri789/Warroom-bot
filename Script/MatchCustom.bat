@echo off

cd ../../../
cd C:\Dropbox\warroom_bot

title MatchCustom 

@echo off
FOR %%A IN (%*) DO (
	echo running matcher_custom %%A
	php index.php matcher_custom bot_auto 9313 %%A
)

::pause
exit