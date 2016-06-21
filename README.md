# iroha Board
iroha Board is a Simple and Easy-to-Use Open Source LMS.

#Project website
http://irohaboard.irohasoft.jp/

#Demo
http://demoib.irohasoft.com/

#System Requirements
* PHP : 5.3.7 or later
* MySQL : 5.1 or later
* CakePHP : 2.7.x

#Instlation
1. Download the source of iroha Board.
https://github.com/irohasoft/irohaboard/releases
* Download the source of CakePHP.
https://github.com/cakephp/cakephp/releases/tag/2.7.11
* Make [cake] directory on your web server and upload the source of CakePHP.
* Upload the source of iroha Board to public direcotry on your web server.  
/cake  
┗ /lib  
/public_html  
┣ /Config  
┣ /Controller  
┣ /Model  
┣ ・・・  
┣ /View  
┗ /webroot  
* Modify the database configuration on Config/database.php file.
Make sure you have created an empty database on you MySQL server.
6. Open http://(your-domain-name)/install on your web browser.

#Features

## For students.

- Learning.
- Take tests.
- Show learning records.
- Show informations from teachers.

## For teachers.
- Manage users.
- Manage user groups.
- Manage informations.
- Manage courses.
- Manage learning contents.
- Manage tests.
- Manage records.

## For administrators.
- System setting

#License
GPLv3
