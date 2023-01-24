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
import re




#data structures:
tgchannel: str = ''
tickers: str = ''
urls: str = ''

class Channels:

    def __init__(self, database) -> None:
        self.connection = pymysql.connect(**sockdata)
        self.cursor = self.connection.cursor()

    def exists(self, url) -> bool:
        with self.connection as connect:
            self.cursor.execute(
                "SELECT * FROM messages WHERE url=? AND;"),
            (url)
            results = self.cursor.fetchall()
            print(results)
            return bool(len(results))

    
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
        global urls
        try:
            with self.connection as connect:
                self.cursor.execute(
                    "SELECT url FROM pages")
                results = self.cursor.fetchall()
        except Exception as ex:
            print(ex)
        for url in results:
            for key, value in url.items():
                urls = urls + ' ' + value


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

# print(tgchannel)
# print(url)
# print(tickers)


# Here page for parse

urls = urls.split()
#print(urls)

for url in urls:
    #print(url)
    with request.urlopen(url) as file:
        src = file.read()
        soup = BeautifulSoup(src, "lxml")
        span_classe = soup.find("div", class_="PulsePost__wrapper_QkcQp")
        find_post_id = re.compile(r'data-post-id="[^"]*"')
        # Находит и вырезает post_id, сохраняет его в переменную
        post_id = find_post_id.findall(str(span_classe))
        post_id = post_id[0].partition('"')[2][:-1]
        #print(post_id)
        print(span_classe.text)
#подготовить спан кляссе к сравнению. 
# if tickers in span_classe:
#         print(tickers.strip())
# Set param for parse page




#if __name__ == "__main__":
    #





###                         with Love from Russia <3                   ###
