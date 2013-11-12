c:
cd \sphinx\bin

net stop SphinxSearch
indexer.exe --all --config c:\sphinx\sphinx_warroom.conf
net start SphinxSearch

cd ../../../
cd Script

@ECHO OFF
@echo Run Warroom Matcher
warroom_matcher.bat