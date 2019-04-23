Leopard (based on Silex)
==============================
The main aim of this project is analyzing PHP file to identify poor programming practices (code smells). 
The system does not analyze any problems preventing correct executing of the code, only following the recommendations for creating 
high quality code. 

The recommendations were obtained during researching literature for professional programmers and academic papers, however, it
should not be used as a teaching or learning tool. 

# Features
## Analysing PHP file to identify potential problems

### Global variables 
Any usage of global variables is marked with warning comment.
### Static methods
Any usage of key word "static" is marked with warning comment.
### Length of a structure:
Structures listed below are checked with regard to their length: 

1. parameter list (4 params)
2. class (250 lines)
3. function (30 lines)
4. line (120 columns)

The length of particular structure can be changed directly in the rule set stored
within the code. 

### Code repetition
The system identifies Type-1 Clones (exact copy and paste) and Type-2 Clones (similarity independent from 
programmer introduced differences such as variable names). 
### Unused variables
Any variable that is used only once within the file is marked with warning as unused. 
This also includes occurrences as a property of a class. 
### Unused methods
Any method that is used only once within the file is marked with warning as unused. The system ignores when a 
function has a public access modifier. 
### Naming standards
All variables and methods are checked against one of the naming conventions listed 
below. Names of constants are checked against constants naming convention (all capitals).
Functions and classes are checked using PascalCase convention. The remaining identifiers can be checked against 
following conventions (chosen when a file for check is added): 

1. camelCase 
2. PascalCase 
3. underscore_convention

### Single letter variables 
The system checks if variables contain at least 2 characters, except when they are a part of for or foreach loop. 
### PHP deprecated methods
Methods are checked against below list of methods identified as deprecated: 
```
call_user_method
call_user_method_array
define_syslog_variables
dl
ereg
ereg_replace
eregi
eregi_replace
mcrypt_generic_end
set_magic_quotes_runtime
magic_quotes_runtime
session_register
session_unregister
session_is_registered
set_socket_blocking
split
spliti
sql_regcase
mysql_db_query
mysql_escape_string
mysql_list_dbs
datefmt_set_timezone_id
mcrypt_cbc
mcrypt_cfb
mcrypt_ecb
mcrypt_ofb
ldap_sort
```

This list is not exhausted and will be updated. 
## Presentation of PHP file 
1. syntax colouring
2. representation of found potential problems and suggestions