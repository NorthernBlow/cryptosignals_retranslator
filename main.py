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
                    "SELECT * FROM tickers")
                results = self.cursor.fetchall()
        except Exception as ex:
            print(ex)
        for ticker in results:
            for key, value in ticker.items():
                if type(value) == str:
                    tickers = value
                    return tickers



    def readtinkoff(self) -> str:
        global url
        try:
            with self.connection as connect:
                self.cursor.execute(
                    "SELECT * FROM pages")
                results = self.cursor.fetchall()
        except Exception as ex:
            print(ex)
        for urls in results:
            for var in urls.values():
                if type(var) == str:
                    url = var
                    return url


    def readtelegram(self) -> str:
        global tgchannel
        try:
            with self.connection as connect:
                self.cursor.execute(
                    "SELECT chan FROM channels")
                results = self.cursor.fetchall()
                for channel in results:
                    for key, value in channel.items():
                        print(value)

        except Exception as ex:
            print(ex)
        

    def close(self):
        self.connection.close()





# channels = Channels(sockdata)
# channels.readtelegram()
channels2 = Channels(sockdata)
channels2.readtelegram()

print(tgchannel)


#if __name__ == "__main__":
    #for var in span_classe:
    #    for ticker in var:
    #        print(ticker.strip())





###                         with Love from Russia <3                   ###
