##########################################################################
###     Hi! This bot monitors the signals from the database,           ###
###     and send notify to all members!                                ###
##########################################################################
###                         For Developers Info:                       ###
###                                                                    ###
###     1. Use tab, not space                                          ###
###     2. Write comments for your code                                ###
##########################################################################
###                         config.py                                  ###
###                                                                    ###
###     This file config database bot and               			   ###
###     other settings.			                                       ###
##########################################################################


import pymysql




sockdata = {
	'host': '127.0.0.1',
	'port': 3306,
	'user': 'root',
	'password': '',
	'database': 'test',
	'cursorclass': pymysql.cursors.DictCursor,
    'init_command': 'SET GLOBAL max_connections = 300'
}

class bcolors:
    HEADER = '\033[95m'
    OKBLUE = '\033[94m'
    OKCYAN = '\033[96m'
    OKGREEN = '\033[92m'
    WARNING = '\033[93m'
    FAIL = '\033[91m'
    ENDC = '\033[0m'
    BOLD = '\033[1m'
    UNDERLINE = '\033[4m'
