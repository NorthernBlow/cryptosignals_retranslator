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

tickers: str = ''

class Channels:
    def __init__(self, database) -> None:
        self.connection = pymysql.connect(**sockdata)
        self.cursor = self.connection.cursor()

    
    def readtickers(self) -> str:
        try:
            with self.connection as connect:
                self.cursor.execute(
                    "SELECT * FROM tickers")
                results = self.cursor.fetchall()
        except Exception as ex:
            print(ex)
        for ticker in results:
            for tickers in ticker.values():
                if type(tickers) == str:
                    return tickers



    def readtinkoff(self) -> str:
        try:
            with self.connection as connect:
                self.cursor.execute(
                    "SELECT * FROM pages")
                results = self.cursor.fetchall()
        except Exception as ex:
            print(ex)
        for url in results:
            for urls in url.values():
                if type(urls) == str:
                    print(urls)
        return url


    def readtelegram(self) -> str:
        try:
            with self.connection as connect:
                self.cursor.execute(
                    "SELECT * FROM channels")
                results = self.cursor.fetchall()
        except Exception as ex:
            print(ex)
        for channel in results:
            for channels in channel.values():
                if type(channels) == str:
                    print(channels)

        return url


    def close(self):
        self.connection.close()





channels = Channels(sockdata)
channels.readtelegram()
channels2 = Channels(sockdata)
channels2.readtinkoff()

print(tickers)


#if __name__ == "__main__":
    #for var in span_classe:
    #    for ticker in var:
    #        print(ticker.strip())





###                         with Love from Russia <3                   ###
