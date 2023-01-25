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
from config import sockdata, API_ID, API_HASH, TOKENTG
import re
from pyrogram import Client, filters




#data structures:
tgchannel: str = ''
tickers: str = ''
urls: str = ''
last_ids: list = []

#test params to send for
params = {
    "source_chat_id": -1001075101206,
    "target_chat_id": -1001789873317,
}


botTG = Client("cryptobot", api_id=API_ID, api_hash=API_HASH,
   bot_token=TOKENTG)




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

    def readtinkoff2(self) -> str:
        global last_ids
        # Тут так-же достаются ID последнего отправленного поста
        try:
            with self.connection as connect:
                self.cursor.execute(
                        "SELECT last_post_id FROM pages")
                results = self.cursor.fetchall()
        except Exception as ex:
            print(ex)
        for last_post_id in results:
            for key, value in last_post_id.items():
                last_ids.append(value)


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


    def parsepage(self) -> str:
        # Here page for parse
        global urls
        global last_ids
        urls = urls.split()
        query_code = []

        try:
            for url in urls:
                with request.urlopen(url) as file:
                    src = file.read()
                    soup = BeautifulSoup(src, "lxml")
                    span_classe = soup.find("div", class_="PulsePost__wrapper_QkcQp")
                    # Находит и вырезает post_id, сохраняет его в переменную
                    find_post_id = re.compile(r'data-post-id="[^"]*"')
                    post_id = find_post_id.findall(str(span_classe))
                    post_id = post_id[0].partition('"')[2][:-1]
                    # Пропускаем пост, если он уже отправлялся
                    print(post_id, '-это пост айди')
                    print(last_ids, '-это ласт айди')
                    match post_id != last_ids:
                        case True:
                            print('True')
                            break
                        case False:
                            with botTG:
                                botTG.send_message(params['target_chat_id'], span_classe.text)
                    #if post_id in last_ids:
                        #pass
                        # if not post_id in last_ids:
                        #     print(span_classe.text)
                        #     with botTG:
                        #         botTG.send_message('@NorthernBlow', 'hueheu')
                    # Записываем ID последнего отправленного поста, из URL источника
                    query_code.append((post_id, url))
                    #print(span_classe.text)
 
            # Тут мы записываем ID последнего пересланного поста
            with self.connection as connect:
                self.cursor.executemany(
                        "UPDATE pages SET last_post_id = %s WHERE url = %s;", query_code)
                connect.commit()

        except Exception as ex:
            print("parsepage func:")
            print(ex)


    def close(self):
        self.connection.close()




channels = Channels(sockdata)
channels.readtinkoff()
channels2 = Channels(sockdata)
channels2.readtelegram()
channels3 = Channels(sockdata)
channels3.readtickers()
channels4 = Channels(sockdata)
channels4.readtinkoff2()

channels5 = Channels(sockdata)
channels5.parsepage()


with botTG:
    print(botTG.export_session_string())


# print(tgchannel)
# print(url)
# print(tickers)


#подготовить спан кляссе к сравнению.
# if tickers in span_classe:
#         print(tickers.strip())
# Set param for parse page




#if __name__ == "__main__":
    #





###                         with Love from Russia <3                   ###
