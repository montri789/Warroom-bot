c:
@echo off
echo ====== Start Reindex %date% %time% >> C:\Script\log_sphinx_reindex\warroom_index_new.txt

cd \sphinx_new\bin

net stop SphinxNew
indexer.exe --all --config c:\sphinx_new\sphinx_warroom.conf >> C:\Script\log_sphinx_reindex\warroom_index_new.txt
net start SphinxNew


timeout /t 100
cd ../../../

echo End Reindex %date% %time% >> C:\Script\log_sphinx_reindex\warroom_index_new.txt
cd Script

echo Run Warroom Matcher
warroom_matcher.bat