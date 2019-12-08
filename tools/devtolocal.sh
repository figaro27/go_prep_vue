#!/bin/bash

ssh goprep "
  ./console/backup_sql.sh
  exit
"

scp forge@165.22.1.106:~/backup_sql.sql $1

mysql -u root -e "drop database goprep";
mysql -u root -e "create database goprep";
mysql -u root goprep < $1
