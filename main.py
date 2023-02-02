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
from pyrogram import Client, filters
from os import environ
from dotenv import load_dotenv
from os.path import join, dirname
import string
import validators




#data structures:
members:        list = []
tgchannel:      str = ''
tickers:        list = []
tickers_pump:   dict = {}
urls:           str = ''
last_ids:       list = []
wordsup:        list = []
wordsdown:      list = []
stopwords:      list = []
settings:       list = []
opt:            dict = {}

dotenv_path = join(dirname(__file__), '.env')
load_dotenv(dotenv_path)




botTG = Client('cryptobot_main', api_id=environ.get('API_ID'), api_hash=environ.get('API_HASH'), bot_token=environ.get('TOKENTG'))
userbotTG = Client('cryptouserbot_main', api_id=environ.get('API_ID'), api_hash=environ.get('API_HASH'))


async def subscribe():
    print('Подписываем на новые телеграм каналы...')
    #print(tgchannel)
    async with userbotTG:
        for channel in tgchannel:
            print('Подписываюсь на ' + channel['chan'])
            await userbotTG.join_chat(channel['chan'])

async def send_signal(user_name, ticker, signal_action, count_members = 0):
    async with botTG:
        print('Отправляю сигнал для ' + user_name)
        if tickers_pump[ticker]:
            pump_num = count_members // int(opt['pump_for_page'])
            if pump_num > 10:
                pump_num = 10
            await botTG.send_message(user_name, "Сигнал: PUMP " + str(pump_num) + " из 10. " + ticker + " " + signal_action)
        else:
            await botTG.send_message(user_name, "Сигнал: " + ticker + " " + signal_action)


class Channels:


    def __init__(self, database) -> None:
        self.connection = pymysql.connect(**sockdata)
        self.cursor = self.connection.cursor()


    def readtickers(self) -> str:
        # Тикеры с ключами записываются в словарь,
        # пример работы с ним ниже:
        #
        #print(tickers[1]['keywords']) # Выведет ключи 2-го по счёту тикера из базы
        global tickers
        try:
            with self.connection as connect:
                self.cursor.execute(
                        "SELECT ticker,keywords,pump FROM tickers")
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
                tgchannel = self.cursor.fetchall()
        except Exception as ex:
            print(ex)

        return tgchannel

    def readmembers(self) -> str:
        global members
        try:
            with self.connection as connect:
                self.cursor.execute(
                        "SELECT user_name FROM members")
                members = self.cursor.fetchall()
        except Exception as ex:
            print(ex)

        return members

    def readsettings(self) -> str:
        global settings
        try:
            with self.connection as connect:
                self.cursor.execute(
                        "SELECT * FROM settings")
                settings = self.cursor.fetchall()
        except Exception as ex:
            print(ex)
        return settings


    def isTickerOrKeywords(self, ticker_and_keywords, post_text, src_url) -> str:

        # Удаляем ВСЕ символы из текста поста,
        # для корректного сравнения.
        # Оригинальный пост сохраняется в post_text
        #
        chars = re.escape(string.punctuation)
        post_clean_text = re.sub(r'['+chars+']', '', post_text)

        # Делаем из словаря список
        #
        stopwords_list: list = []
        for stopword in stopwords:
            for value in stopword.values():
                stopwords_list = stopwords_list + value.split(',')
        stopwords_list = map(str.lower, stopwords_list)

        for ticker in tickers:
            if ticker['pump']:
                tickers_pump[ticker['ticker']] = 1
            else:
                tickers_pump[ticker['ticker']] = 0

        for pump in settings:
            opt[pump['name']] = pump['value']

        # Парсим стоп слова в тесте поста
        #
        sandbox = set(stopwords_list) & set(post_clean_text.split())
        if sandbox:
            print("В песочницу! Стоп слово: " + str(sandbox))
            # Тут мы записываем пост в базу
            query_sandbox = [(src_url, post_text, "Стоп слово " + str(sandbox))]
            self.cursor.executemany(
                    "INSERT INTO sandbox (src, post, reason) VALUES (%s, %s, %s);", query_sandbox)
        else:
            ticker_name: str = ""
            ticker_count: int = 0
            signal_action: str = ""
            for ticker in ticker_and_keywords:

                if ticker.lower() in post_clean_text or set(ticker_and_keywords[ticker]) & set(post_clean_text.split()):
                    print("Найден тикер " + ticker + ", парсим пост.")
                    ticker_name = ticker
                    ticker_count += 1

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
                        if word_for_up in post_clean_text:
                            signal_action = "повышение"
                            print("Обнаружен сигнал на повышение для " + ticker  + ", триггер: " + str(word_for_up)) 


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
                        if word_for_down in post_clean_text:
                            signal_action = "понижение"
                            print("Обнаружен сигнал на понижение для " + ticker + ", триггер: " + str(word_for_down))


            if ticker_count == 1 and len(signal_action) < 3:
                print("Маркеров для сигнала не найдено. " + ticker)
                query_sandbox = [(src_url, post_text, "Нет маркеров (" + ticker_name + ")")]
                self.cursor.executemany(
                        "INSERT INTO sandbox (src, post, reason) VALUES (%s, %s, %s);", query_sandbox)
            elif ticker_count == 1:
                print('ОТПРАВЛЯЮ СИГНАЛ!')
                members_list: list = []
                for user_name in members:
                    for value in user_name.values():
                        botTG.run(send_signal(value, ticker_name, signal_action))
            elif ticker_count >= 2:
                print("Найдено больше 1 тикера в посте!")
                query_sandbox = [(src_url, post_text, "Больше 1 тикера")]
                self.cursor.executemany(
                        "INSERT INTO sandbox (src, post, reason) VALUES (%s, %s, %s);", query_sandbox)
            else:
                print("Совпадений нет")
                query_sandbox = [(src_url, post_text, "Нет тикеров")]
                self.cursor.executemany(
                        "INSERT INTO sandbox (src, post, reason) VALUES (%s, %s, %s);", query_sandbox)


    def parsepage(self) -> str:
        # Here page for parse
        global urls
        global last_ids
        urls = urls.split()
        query_code = []


        try:
            for url in urls:
                if validators.url(url) != True:
                    print('Skip not valid URL!')
                    continue
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
                            ticker_and_keywords: dict = {}
                            for ticker in tickers:
                                ticker_and_keywords[ticker['ticker']] = ticker['keywords'].split(',')

                            #ticker_and_keywords = map(str.lower, ticker_and_keywords) # Переводит список тикеров из базы в нижний регистр
                            self.isTickerOrKeywords(ticker_and_keywords, post_div.text.lower(), url) # Отправляет текст поста в нижнем регистре в функцию парсинга поста на тикеры и ключи


                    # Записываем ID последнего отправленного поста, из URL источника
                    query_code.append((post_id, url))
                    #print(span_classe.text)
 
            # Тут мы записываем ID последнего пересланного поста
            with self.connection as connect:
                self.cursor.executemany(
                        "UPDATE pages SET last_post_id = %s WHERE url = %s;", query_code)
                connect.commit()

        except Exception as ex:
            print("parsepage func:", ex)


    def close(self):
        self.connection.close()


channels = Channels(sockdata)
channels.readtinkoff()
channels0 = Channels(sockdata)
channels0.readsettings()
channels1 = Channels(sockdata)
channels1.readmembers()
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
userbotTG.run(subscribe())


###                         with Love from Russia <3                   ###
