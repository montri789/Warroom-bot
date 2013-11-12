@echo off

cd ../../../
cd C:\Dropbox\warroom_bot

title BotClear 

@echo off
FOR %%B IN (%*) DO (
	echo running clear bot %%B	
	php index.php matcher_custom clear %%B
)

::pause
exit