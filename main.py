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
from os import environ
from dotenv import load_dotenv
from os.path import join, dirname



#data structures:
tgchannel: str = ''
tickers: list = []
urls: str = ''
last_ids: list = []
wordsup: list = []
wordsdown: list = []
stopwords: list = []

dotenv_path = join(dirname(__file__), '.env')
load_dotenv(dotenv_path)



#test params to send for
params = {
    "source_chat_id": -1001075101206,
    "target_chat_id": -1001789873317,
}


botTG = Client("cryptobot", api_id=environ.get('API_ID'), api_hash=environ.get('API_HASH'),
   bot_token=environ.get('TOKENTG'))




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
        # Тикеры с ключами записываются в словарь,
        # пример работы с ним ниже:
        #
        #print(tickers[1]['keywords']) # Выведет ключи 2-го по счёту тикера из базы
        global tickers
        try:
            with self.connection as connect:
                self.cursor.execute(
                        "SELECT ticker,keywords FROM tickers")
                tickers = self.cursor.fetchall()
        except Exception as ex:
            print(ex)
  

    def readstopwords(self) -> str:
        # Словарь стоп слов,
        # пример работы с ними ниже:
        #
        #print(stopword[0]['stopword']) # Выведет 1-ю слово/фразу для отправки поста в песочницу
        global stopwords
        try:
            with self.connection as connect:
                self.cursor.execute(
                        "SELECT stopword FROM stopwords")
                stopwords = self.cursor.fetchall()
        except Exception as ex:
            print(ex)


    def readwordsup(self) -> str:
        # Словарь для сигналов повышения,
        # пример работы с ними ниже:
        #
        #print(wordsup[0]['word_for_up']) # Выведет 1-ю слово/фразу для сигнала повышения
        global wordsup
        try:
            with self.connection as connect:
                self.cursor.execute(
                        "SELECT word_for_up FROM wordsup")
                wordsup = self.cursor.fetchall()
        except Exception as ex:
            print(ex)


    def readwordsdown(self) -> str:
        # Словарь для сигналов понижения,
        # пример работы с ними ниже:
        #
        #print(wordsdown[0]['word_for_down']) # Выведет 1-ю слово/фразу для сигнала понижения
        global wordsdown
        try:
            with self.connection as connect:
                self.cursor.execute(
                        "SELECT word_for_down FROM wordsdown")
                wordsdown = self.cursor.fetchall()
        except Exception as ex:
            print(ex)


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


    def isTickerOrKeywords(self, ticker_and_keywords, post_text, src_url) -> str:
        #print(ticker_and_keywords)
        #print(post_text.split())

        # Делаем из словаря список
        #
        stopwords_list: list = []
        for stopword in stopwords:
            for value in stopword.values():
                stopwords_list = stopwords_list + value.split(',')
        stopwords_list = map(str.lower, stopwords_list)

        # Парсим стоп слова в тесте поста
        #
        sandbox = set(stopwords_list) & set(post_text.replace("$", "").split())
        if sandbox:
            print("В песочницу! Стоп слово: " + str(sandbox))
            # Тут мы записываем пост в базу
            query_sandbox = [(src_url, post_text, "Стоп слово " + str(sandbox))]
            self.cursor.executemany(
                    "INSERT INTO sandbox (src, post, reason) VALUES (%s, %s, %s);", query_sandbox)
        else:
            tmp = set(ticker_and_keywords) & set(post_text.replace("$", "").split())
            if tmp:
                print("Найдены совпадения, парсим пост: " + str(tmp))




                # Парсим слова/фразы для сигналов на повышение
                # Делаем из словаря список
                #
                wordsup_list: list = []
                for word_for_up in wordsup:
                    for value in word_for_up.values():
                        wordsup_list = wordsup_list + value.split(',')
                wordsup_list = map(str.lower, wordsup_list)

                # Такой способ ищет слова и фразы в тесте поста
                #
                for word_for_up in wordsup_list:
                    if word_for_up in post_text:
                        print("Отправлен сигнал на повышение для " + str(tmp) + ", триггер: " + str(word_for_up))

                # А этот способ только отдельные слова
                #
                #signal_up = set(wordsup_list) & set(post_text.split())
                #if signal_up:
                #    print("Отправляем сигнал повышение для " + str(tmp) + ", триггер: "+ str(signal_up))




                # Парсим слова/фразы для сигналов на понижение
                # Делаем из словаря список
                #
                wordsdown_list: list = []
                for word_for_down in wordsdown:
                    for value in word_for_down.values():
                        wordsdown_list = wordsdown_list + value.split(',')
                wordsdown_list = map(str.lower, wordsdown_list)

                # Такой способ ищет слова и фразы в тесте поста
                #
                for word_for_down in wordsdown_list:
                    if word_for_down in post_text:
                        print("Отправлен сигнал на понижение для " + str(tmp) + ", триггер: " + str(word_for_down))
                # А этот способ только отдельные слова
                #
                #signal_down = set(wordsdown_list) & set(post_text.split())
                #if signal_down:
                #    print("Отправляем сигнал на понижение для " + str(tmp) + ", триггер: " + str(signal_down))

            else:
                print("Совпадений нет")

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
                    post_div = span_classe.find("div", class_="TextLineCollapse__sizeS_BxRAe")
                    # Находит и вырезает post_id, сохраняет его в переменную
                    find_post_id = re.compile(r'data-post-id="[^"]*"')
                    post_id = find_post_id.findall(str(span_classe))
                    post_id = post_id[0].partition('"')[2][:-1]
                    # Пропускаем пост, если он уже отправлялся
                    #print(post_id, '-это пост айди')
                    #print(last_ids, '-это ласт айди')

                    
                    match str(post_id).strip('[]') in str(last_ids).strip('[]'):
                        case True:
                            print('Не отправляем пост', post_id) # Если пост уже отправлялся ранее
                            
                        case False:
                            ticker_and_keywords: list = []
                            for ticker in tickers:
                                for value in ticker.values():
                                    ticker_and_keywords = ticker_and_keywords + value.split(',')
                                    #with botTG:
                                        #botTG.send_message(params['target_chat_id'], span_classe.text)
                            ticker_and_keywords = map(str.lower, ticker_and_keywords) # Переводит список тикеров из базы в нижний регистр
                            self.isTickerOrKeywords(ticker_and_keywords, post_div.text.lower(), url) # Отправляет текст поста в нижнем регистре в функцию парсинга поста на тикеры и ключи

                            
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
channels5.readwordsup()
channels6 = Channels(sockdata)
channels6.readwordsdown()
channels7 = Channels(sockdata)
channels7.readstopwords()

channels8 = Channels(sockdata)
channels8.parsepage()


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
