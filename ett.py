import pypyodbc as pyodbc
from ftplib import FTP

ftp = FTP('ftp.eidosconsultores.com')     # connect to host, default port
ftp.login('eidoscon', 'eIdos_con2016')

ftp.cwd('public_html')
ftp.retrlines('LIST')

#Connection_String = 'Driver={Oracle in OraClient11g_home1};DBQ=SPITEST;Uid=CZNDB;Pwd=CZNDB;'
#connection = pyodbc.connect(Connection_String)
#print(connection)