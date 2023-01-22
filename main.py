##########################################################################
###     Hi! This bot monitors the signals from the database,           ###
###     and send notify to all members!                                ###
##########################################################################
###                         For Developers Info:                       ###
###                                                                    ###
###     1. Use tab, not space                                          ###
###     2. Write comments for your code                                ###
##########################################################################


import requests
from bs4 import BeautifulSoup
import lxml
import os
from urllib import request
import pymysql
from config import sockdata

# Here page for parse, later move to Database!!!
url = 'https://www.tinkoff.ru/invest/social/profile/De_vint/'
with request.urlopen(url) as file:
    src = file.read()

# Set param for parse page
soup = BeautifulSoup(src, "lxml")
span_classe = soup.find_all("span", class_="TickerWithTooltip__ticker_YdPIW")



#test data structures:

tickers = []


class Channels:
    def __init__(self, database):
        self.connection = pymysql.connect(**sockdata)
        self.cursor = self.connection.cursor()

    # def exists(self, chan):
    def readtinkoff(self):
        try:
            with self.connection as connect:
                self.cursor.execute(
                    "SELECT * FROM pages")
                results = self.cursor.fetchall()
                print(results)

        except Exception as ex:
            print(ex)

    def readtelegram(self):
        try:
            with self.connection as connect:
                self.cursor.execute(
                    "SELECT * FROM channels")
                results = self.cursor.fetchall()
                print(results)
        except Exception as ex:
            print(ex)

    def close(self):
        self.connection.close()

channels = Channels(sockdata)
channels.readtelegram()
channels2 = Channels(sockdata)
channels2.readtinkoff()

# Connect to Database
# try:
#     connection = pymysql.connect(
#         host=host,
#         port=3306,
#         user=user,
#         password=password,
#         database=db_name,
#         cursorclass=pymysql.cursors.DictCursor
#     )
#     print("Подключился к базе данных")
# # Here send message for failed
# except Exception as ex:
#     print("Подключение сброшено :(")
#     print(ex)




def GetTinkoffURLS():
    try:
        with connection.cursor() as cursor:
            select_all_raws = "SELECT * FROM pages"
            cursor.execute(select_all_raws)
            print("выбрали все из бд")
            rows = cursor.fetchall()
            for row in rows:
                print(rows)
    except Exception as ex:
        print(ex)

    return


def GetTickers():
    return


def GetKeywords():
    return


# Here tmp solution
def GetTelegramChannels():
    # Here get signal from Database
    try:
        with connection.cursor() as cursor:
            select_all_raws = "SELECT chan FROM channels GROUP BY id HAVING id > 0"
            cursor.execute(select_all_raws)
            print("выбрали все из бд")
            rows = cursor.fetchall()
            for row in rows:
                print(rows)
    finally:
        connection.close()


#if __name__ == "__main__":
    #for var in span_classe:
    #    for ticker in var:
    #        print(ticker.strip())

    # GetTelegramChannels()
    # GetTinkoffURLS()




###                         with Love from Russia <3                   ###
