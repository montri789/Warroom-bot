c:
cd \sphinx\bin

net stop SphinxSearch
indexer.exe --all --config c:\sphinx\sphinx_warroom.conf
net start SphinxSearch