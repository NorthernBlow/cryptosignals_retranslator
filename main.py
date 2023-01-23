##########################################################################
###     Hi! This bot monitors the signals from the database,           ###
###     and send notify to all members!                                ###
##########################################################################
###                         For Developers Info:                       ###
###                                                                    ###
###     1. Use tab, not space                                          ###
###     2. Write comments for your code                                ###
##########################################################################


from bs4 import BeautifulSoup
import lxml
from urllib import request
import pymysql
from config import sockdata

# Here page for parse, later move to Database!!!
url: str = 'https://www.tinkoff.ru/invest/social/profile/De_vint/'
with request.urlopen(url) as file:
    src = file.read()

# Set param for parse page
soup = BeautifulSoup(src, "lxml")
span_classe = soup.find_all("span", class_="TickerWithTooltip__ticker_YdPIW")



#test data structures:
tgchannel: str = ''
tickers: str = ''

class Channels:

    def __init__(self, database) -> None:
        self.connection = pymysql.connect(**sockdata)
        self.cursor = self.connection.cursor()

    
    def readtickers(self) -> str:
        global tickers
        try:
            with self.connection as connect:
                self.cursor.execute(
                    "SELECT ticker FROM tickers")
                results = self.cursor.fetchall()
        except Exception as ex:
            print(ex)
        for ticker in results:
            for key, value in ticker.items():
                    tickers = tickers + ' ' + value



    def readtinkoff(self) -> str:
        global url
        try:
            with self.connection as connect:
                self.cursor.execute(
                    "SELECT url FROM pages")
                results = self.cursor.fetchall()
        except Exception as ex:
            print(ex)
        for urls in results:
            for key, value in urls.items():
                url = url + ' ' + value


    def readtelegram(self) -> str:
        global tgchannel
        try:
            with self.connection as connect:
                self.cursor.execute(
                    "SELECT chan FROM channels")
                results = self.cursor.fetchall()
                for channel in results:
                    for key, value in channel.items():
                        tgchannel = tgchannel + ' ' + value

        except Exception as ex:
            print(ex)
        

    def close(self):
        self.connection.close()





channels = Channels(sockdata)
channels.readtinkoff()
channels2 = Channels(sockdata)
channels2.readtelegram()
channels3 = Channels(sockdata)
channels3.readtickers()

print(tgchannel)
print(url)
print(tickers)

#if __name__ == "__main__":
    #for var in span_classe:
    #    for ticker in var:
    #        print(ticker.strip())





###                         with Love from Russia <3                   ###
