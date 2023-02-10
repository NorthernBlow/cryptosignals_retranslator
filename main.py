##########################################################################
###     Hi! This bot monitors the signals from the database,           ###
###     and send notify to all members!                                ###
##########################################################################
###                         For Developers Info:                       ###
###                                                                    ###
###     1. Use tab, not space                                          ###
###     2. Write comments for your code                                ###
##########################################################################
###                         main.py                                    ###
###                                                                    ###
###     This file parse source pages, and send signal or               ###
###     add post in sandbox.                                           ###
##########################################################################


from bs4 import BeautifulSoup
import lxml
from urllib import request
import pymysql
from config import sockdata
from config import bcolors
import re
from pyrogram import Client, filters, idle
from os import environ
from dotenv import load_dotenv
from os.path import join, dirname
import string
import validators
from threading import Thread
import threading
import datetime




#data structures:
members:        list = []
member_status:  dict = {}
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
posts:          list = []


dotenv_path = join(dirname(__file__), '.env')
load_dotenv(dotenv_path)




botTG = Client('cryptobot_main', api_id=environ.get('API_ID'), api_hash=environ.get('API_HASH'), bot_token=environ.get('TOKENTG'))
userbotTG = Client('cryptouserbot_main', api_id=environ.get('API_ID'), api_hash=environ.get('API_HASH'))


async def subscribe():
    #
    #
    # This function userbot, for subscribe on telegram channels from sources database.
    # tgchannel — global var for list channels
    #
    # print(bcolors.ENDC + '~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~')
    # print('Проверяю новые телеграм каналы в источниках…')
    async with userbotTG:
        for channel in tgchannel:
            try:
                await userbotTG.get_chat_member(channel['chan'], 'me')
            except Exception as ex:
                print(bcolors.OKCYAN + ' Подписался на ' + bcolors.ENDC + '@' + channel['chan'])
                await userbotTG.join_chat(channel['chan'])
    # print('~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~')


def send_signal(user_name: str, ticker: str, signal_action: str, count_members: int = 0) -> None:
    #
    #
    # This function bot, for send signal to @user_name
    #
    # args:
    # user_name - username telegram
    # ticker - ticker name
    # signal_action - up or down
    # count_members - source members num
    #
    print(bcolors.OKBLUE + ' Отправляю сигнал для ' + bcolors.ENDC + '@' + user_name)
    pump_signal = ''
    if tickers_pump[ticker]:
        pump_num = count_members // int(opt['pump_for_page'])
        if pump_num > 10:
            pump_num = 10
        if pump_num > 0:
            pump_signal = 'PUMP ' + str(pump_num) + " из 10."
    botTG.send_message(user_name, "Сигнал: " + pump_signal + " " + ticker + " " + signal_action)


class Cryptobot:
        

    def reinit(self, database) -> None:
        #
        # This function for get data from database
        #
        # posts, tickers, stopwords, words for signal up and down, 
        # url pages source, last post ids, username for telegram channels,
        # members bot, settings
        global posts
        global tickers
        global stopwords
        global wordsup
        global wordsdown
        global urls
        global last_ids
        global tgchannel
        global members
        global settings


        self.connection = pymysql.connect(**sockdata)
        self.cursor = self.connection.cursor()


        try:
            urls = ''
            last_ids = []
            with self.connection as connect:
                # GET POSTS FROM DB
                try:
                    self.cursor.execute(
                            "SELECT post FROM sandbox")
                    posts = self.cursor.fetchall()
                except Exception as ex:
                    print(bcolors.FAIL + '\n Ошибка! Не удалось прочитать песочницу (sandbox) :(\n', ex)

                # GET TICKERS FROM DB
                try:
                    self.cursor.execute(
                            "SELECT ticker,keywords,pump FROM tickers")
                    tickers = self.cursor.fetchall()
                    for ticker in tickers:
                        if ticker['pump']:
                            tickers_pump[ticker['ticker']] = 1
                        else:
                            tickers_pump[ticker['ticker']] = 0
                except Exception as ex:
                    print(bcolors.FAIL + '\n Ошибка! Не удалось прочитать тикеры (tickers) :(\n', ex)

                # GET STOP WORDS FROM DB
                try:
                    self.cursor.execute(
                            "SELECT stopword FROM stopwords")
                    stopwords = self.cursor.fetchall()
                    # Делаем из словаря список
                    #
                    self.stopwords_list: list = []
                    for stopword in stopwords:
                        for value in stopword.values():
                            self.stopwords_list = self.stopwords_list + value.split(',')
                    self.stopwords_list = map(str.lower, self.stopwords_list)
                except Exception as ex:
                    print(bcolors.FAIL + '\n Ошибка! Не удалось прочитать стоп слова (stopwords) :(\n', ex)

                # GET WORDS FOR UP SIGNAL FROM DB
                try:
                    self.cursor.execute(
                            "SELECT word_for_up FROM wordsup")
                    wordsup = self.cursor.fetchall()
                    # Парсим слова/фразы для сигналов на повышение
                    # Делаем из словаря список
                    #
                    self.wordsup_list: list = []
                    for word_for_up in wordsup:
                        for value in word_for_up.values():
                            self.wordsup_list = self.wordsup_list + value.split(',')
                    self.wordsup_list = map(str.lower, self.wordsup_list)
                except Exception as ex:
                    print(bcolors.FAIL + '\n Ошибка! Не удалось прочитать слова на повышение (wordsup) :(\n', ex)

                # GET WORDS FOR DOWN SIGNAL FROM DB
                try:
                    self.cursor.execute(
                            "SELECT word_for_down FROM wordsdown")
                    wordsdown = self.cursor.fetchall()
                    # Парсим слова/фразы для сигналов на понижение
                    # Делаем из словаря список
                    #
                    self.wordsdown_list: list = []
                    for word_for_down in wordsdown:
                        for value in word_for_down.values():
                            self.wordsdown_list = self.wordsdown_list + value.split(',')
                    self.wordsdown_list = map(str.lower, self.wordsdown_list)
                except Exception as ex:
                    print(bcolors.FAIL + '\n Ошибка! Не удалось прочитать слова на понижение (wordsdown) :(\n', ex)

                # GET URL PAGES SOURCE FROM DB
                try:
                    self.cursor.execute(
                            "SELECT url FROM pages")
                    results = self.cursor.fetchall()
                    for url in results:
                        for key, value in url.items():
                            urls = urls + ' ' + value
                    urls = urls.split()
                except Exception as ex:
                    print(bcolors.FAIL + '\n Ошибка! Не удалось прочитать страницы источников (pages) :(\n', ex)

                # GET LAST PARSE POST ID FROM DB
                try:
                    self.cursor.execute(
                            "SELECT last_post_id FROM pages")
                    results = self.cursor.fetchall()
                    for last_post_id in results:
                        for key, value in last_post_id.items():
                            last_ids.append(value)
                except Exception as ex:
                    print(bcolors.FAIL + '\n Ошибка! Не удалось прочитать последние распарсенные посты (last post id from pages) :(\n', ex)

                # GET USERNAME TELEGRAM CHANNELS FROM DB
                try:
                    self.cursor.execute(
                            "SELECT chan FROM channels")
                    tgchannel = self.cursor.fetchall()
                except Exception as ex:
                    print(bcolors.FAIL + '\n Ошибка! Не удалось прочитать телеграм каналы источники (channels) :(\n', ex)

                # GET USERNAME TELEGRAM FOR MEMBERS BOT FROM DB
                try:
                    self.cursor.execute(
                            "SELECT user_name FROM members")
                    members = self.cursor.fetchall()
                except Exception as ex:
                    print(bcolors.FAIL + '\n Ошибка! Не удалось прочитать подписчиков бота (members) :(\n', ex)

                # GET USERNAME STATUS FOR MEMBERS BOT FROM DB
                try:
                    self.cursor.execute(
                            "SELECT user_name,status FROM members")
                    tmp = self.cursor.fetchall()
                    for member in tmp:
                        if member['status']:
                            member_status[member['user_name']] = 1
                        else:
                            member_status[member['user_name']] = 0
                except Exception as ex:
                    print(bcolors.FAIL + '\n Ошибка! Не удалось прочитать подписчиков бота (members) :(\n', ex)

                # GET SETTINGS FROM DB
                try:
                    self.cursor.execute(
                            "SELECT * FROM settings")
                    settings = self.cursor.fetchall()
                    for pump in settings:
                        opt[pump['name']] = pump['value']
                except Exception as ex:
                    print(bcolors.FAIL + '\n Ошибка! Не удалось прочитать настройки бота (settings) :(\n', ex)

                now = datetime.datetime.now();
                print(bcolors.ENDC, now.strftime('%d %b %H:%M (%a)'), bcolors.OKGREEN + ' Данные успешно загружены из базы' + bcolors.ENDC + ' OK')

                try:
                    for url in urls:
                        if validators.url(url) != True:
                            print(bcolors.WARNING + ' Пропускаю некорректный URL источника: ' + bcolors.ENDC + url)
                            continue
                        th = Thread(target=self.parsePage, args=(url, ))
                        th.start()
                except Exception as ex:
                    print(bcolors.FAIL + '\n Ошибка! Не удалось корректно выполнить все потоки парсера :(\n', ex)

                threading.Timer(60.0, self.reinit, [database]).start()

        except Exception as ex:
            print(bcolors.FAIL + '\n Ошибка! Не удалось соединиться с базой данных или другая системная ошибка, извини, точнее не скажу :(\n', ex)


    def parse_subscribers_num(self, url: str) -> int:
        """Функция для получения числа подписчиков из тинькофф.инвестиций
        args: url - строковое представление url
        return: возвращает число подписчиков пульсят по указанному url типа integer
        """
        with request.urlopen(url) as file:
            src = file.read()
            soup = BeautifulSoup(src, "lxml")
            subscr_classe = soup.find("div", class_="ProfileInfo__info_cfaff")
            subscr_div = subscr_classe.find("span", class_="ProfileInfo__socialityNumber_yzn49")
            
            subscr_div = subscr_div.text.replace(' ', '') #поудалял пробелы
            
            re.sub(r'\D', '', 'subscr_div') # здесь оставляю только строку с цифрами
            subscr_div = int(subscr_div)    # типа integer 94485
            # print("это число подписчиков в тинькофф ->", subscr_div) 
            return subscr_div


    def isTickerOrKeywords(self, ticker_and_keywords, post_text, src_url) -> str:

        connection = pymysql.connect(**sockdata)
        cursor = connection.cursor()

        # Удаляем ВСЕ символы из текста поста,
        # для корректного сравнения.
        # Оригинальный пост сохраняется в post_text
        #
        chars = re.escape(string.punctuation)
        post_clean_text = re.sub(r'['+chars+']', '', post_text.lower())

        
        # Парсим стоп слова в тексте поста
        #
        sandbox = list(set(self.stopwords_list) & set(post_clean_text.split()))

        if sandbox:
            print(bcolors.OKCYAN + " Найдено стоп слово: " + sandbox[0])
            # Тут мы записываем пост в базу
            query_sandbox = [(src_url, post_text, "Стоп слово " + sandbox[0])]
            try:
                with connection as connect:
                    cursor.executemany(
                            "INSERT INTO sandbox (src, post, reason) VALUES (%s, %s, %s);", query_sandbox)
                    connect.commit()
                print(bcolors.OKCYAN + ' Добавил пост в песочницу')
            except Exception as ex:
                print(bcolors.FAIL + '\n Не удалось добавить пост в песочницу :(\n', ex)

        else:
            ticker_name:    str = ""
            tickers_arr:    list = []
            ticker_count:   int = 0
            signal_action:  str = ""

            for ticker in ticker_and_keywords:

                if ticker.lower() in post_clean_text or list(set(ticker_and_keywords[ticker]) & set(post_clean_text.split())):
                    print(bcolors.OKCYAN + " Найден тикер " + ticker + "…") # или ключевое слово
                    # print(list(set(ticker_and_keywords[ticker]) & set(post_clean_text.split())))

                    ticker_name = ticker
                    ticker_count += 1

                    if list(set(ticker_and_keywords[ticker]) & set(post_clean_text.split())):
                        tickers_arr.append(list(set(ticker_and_keywords[ticker]) & set(post_clean_text.split()))[0])
                    else:
                        tickers_arr.append(ticker)

                
            if ticker_count == 1:

                # Такой способ ищет слова и фразы в тексте поста
                #
                for word_for_up in self.wordsup_list:
                    if word_for_up in post_clean_text:
                        signal_action = "повышение"
                        print(bcolors.OKCYAN + " Обнаружен сигнал на повышение для " + bcolors.ENDC + ticker + bcolors.OKCYAN + ", триггер: " + bcolors.ENDC + str(word_for_up)) 


                # Такой способ ищет слова и фразы в тексте поста
                #
                for word_for_down in self.wordsdown_list:
                    if word_for_down in post_clean_text:
                        signal_action = "понижение"
                        print(bcolors.OKCYAN + " Обнаружен сигнал на понижение для " + bcolors.ENDC + ticker + bcolors.OKCYAN + ", триггер: " + bcolors.ENDC + str(word_for_down))

                if len(signal_action) < 3:
                    print(bcolors.OKCYAN + " " + ticker_name + ", маркеров для сигнала не найдено, отправляю в песочницу.")
                    query_sandbox = [(src_url, post_text, "Нет маркеров (" + ticker_name + ")")]
                    try:
                        with connection as connect:
                            cursor.executemany(
                                    "INSERT INTO sandbox (src, post, reason) VALUES (%s, %s, %s);", query_sandbox)
                            connect.commit()
                    except Exception as ex:
                        print(bcolors.FAIL + '\n Не удалось добавить пост в песочницу :(\n', ex)
                else:
                    print(bcolors.OKGREEN + ' Отправляю сигнал ' + bcolors.ENDC + ticker_name + ' ' + signal_action + bcolors.OKGREEN + ' всем подписчикам')
                    for user_name in members:
                        for value in user_name.values():
                            if member_status[value]:
                                send_signal(value, ticker_name, signal_action, self.parse_subscribers_num(src_url))
                            else:
                                print(bcolors.ENDC + ' У @' + value + ' закончилась подписка')

            elif ticker_count > 1:
                print(bcolors.OKCYAN + " Найдено больше 1 тикера в посте!")
                num_ticker = 0

                with connection as connect:
                    while num_ticker < len(tickers_arr):
                        s = post_clean_text.partition(tickers_arr[num_ticker].lower())
                        ss = s[2][0:100].partition('\n\n')
                        next_ticker = num_ticker + 1

                        if 0 <= next_ticker < len(tickers_arr):
                            sss = s[2][0:100].partition(tickers_arr[next_ticker].lower())

                        if len(ss[2]) > 0:
                            print(bcolors.ENDC + ' Текущий сигнал ищу до двойного переноса в части поста')
                            # print('>>> ', ss[0][0:100]) # часть текста поста для поиска в нём слов из словаря сигналов
                            # Такой способ ищет слова и фразы в тексте поста
                            #
                            for word_for_up in self.wordsup_list:
                                if word_for_up in ss[0][0:100]:
                                    signal_action = "повышение"
                                    print(bcolors.OKCYAN + " Обнаружен сигнал на повышение для " + bcolors.ENDC + tickers_arr[num_ticker] + bcolors.OKCYAN + ", триггер: " + bcolors.ENDC + str(word_for_up)) 


                            # Такой способ ищет слова и фразы в тексте поста
                            #
                            for word_for_down in self.wordsdown_list:
                                if word_for_down in ss[0][0:100]:
                                    signal_action = "понижение"
                                    print(bcolors.OKCYAN + " Обнаружен сигнал на понижение для " + bcolors.ENDC + tickers_arr[num_ticker] + bcolors.OKCYAN + ", триггер: " + bcolors.ENDC + str(word_for_down))

                            if len(signal_action) < 3:
                                print(bcolors.OKCYAN + " " + tickers_arr[num_ticker] + ", маркеров для сигнала не найдено, отправляю в песочницу.")
                                query_sandbox = [(src_url, ss[0][0:100], "Нет маркеров (" + tickers_arr[num_ticker] + ")")]
                                try:
                                    cursor.executemany(
                                            "INSERT INTO sandbox (src, post, reason) VALUES (%s, %s, %s);", query_sandbox)
                                except Exception as ex:
                                    print(bcolors.FAIL + '\n Не удалось добавить пост в песочницу :(\n', ex)
                            else:
                                print(bcolors.OKGREEN + ' Отправляю сигнал ' + bcolors.ENDC + tickers_arr[num_ticker] + ' ' + signal_action + bcolors.OKGREEN + ' всем подписчикам')
                                for user_name in members:
                                    for value in user_name.values():
                                        if member_status[value]:
                                            send_signal(value, tickers_arr[num_ticker], signal_action, self.parse_subscribers_num(src_url))
                                        else:
                                            print(bcolors.ENDC + ' У @' + value + ' закончилась подписка')

                            num_ticker +=1
                            continue

                        if len(sss[2]) > 0:
                            print(bcolors.ENDC + ' Текущий сигнал ищу до упоминания следующего тикера в части поста')
                            # print('>>> ', sss[0][0:100]) # часть текста поста для поиска в нём слов из словаря сигналов
                            # Такой способ ищет слова и фразы в тексте поста
                            #
                            for word_for_up in self.wordsup_list:
                                if word_for_up in sss[0][0:100]:
                                    signal_action = "повышение"
                                    print(bcolors.OKCYAN + " Обнаружен сигнал на повышение для " + bcolors.ENDC + tickers_arr[num_ticker] + bcolors.OKCYAN + ", триггер: " + bcolors.ENDC + str(word_for_up)) 


                            # Такой способ ищет слова и фразы в тексте поста
                            #
                            for word_for_down in self.wordsdown_list:
                                if word_for_down in sss[0][0:100]:
                                    signal_action = "понижение"
                                    print(bcolors.OKCYAN + " Обнаружен сигнал на понижение для " + bcolors.ENDC + tickers_arr[num_ticker] + bcolors.OKCYAN + ", триггер: " + bcolors.ENDC + str(word_for_down))

                            if len(signal_action) < 3:
                                print(bcolors.OKCYAN + " " + tickers_arr[num_ticker] + ", маркеров для сигнала не найдено, отправляю в песочницу.")
                                query_sandbox = [(src_url, sss[0][0:100], "Нет маркеров (" + tickers_arr[num_ticker] + ")")]
                                try:
                                    cursor.executemany(
                                            "INSERT INTO sandbox (src, post, reason) VALUES (%s, %s, %s);", query_sandbox)
                                except Exception as ex:
                                    print(bcolors.FAIL + '\n Не удалось добавить пост в песочницу :(\n', ex)
                            else:
                                print(bcolors.OKGREEN + ' Отправляю сигнал ' + bcolors.ENDC + tickers_arr[num_ticker] + ' ' + signal_action + bcolors.OKGREEN + ' всем подписчикам')
                                for user_name in members:
                                    for value in user_name.values():
                                        if member_status[value]:
                                            send_signal(value, tickers_arr[num_ticker], signal_action, self.parse_subscribers_num(src_url))
                                        else:
                                            print(bcolors.ENDC + ' У @' + value + ' закончилась подписка')

                            num_ticker +=1
                            continue

                        print(bcolors.ENDC + ' Текущий сигнал ищу в 100 символов части поста')
                        # print('>>> ', s[2][0:100]) # часть текста поста для поиска в нём слов из словаря сигналов
                        # Такой способ ищет слова и фразы в тексте поста
                        #
                        for word_for_up in self.wordsup_list:
                            if word_for_up in s[2][0:100]:
                                signal_action = "повышение"
                                print(bcolors.OKCYAN + " Обнаружен сигнал на повышение для " + bcolors.ENDC + tickers_arr[num_ticker] + bcolors.OKCYAN + ", триггер: " + bcolors.ENDC + str(word_for_up)) 


                        # Такой способ ищет слова и фразы в тексте поста
                        #
                        for word_for_down in self.wordsdown_list:
                            if word_for_down in s[2][0:100]:
                                signal_action = "понижение"
                                print(bcolors.OKCYAN + " Обнаружен сигнал на понижение для " + bcolors.ENDC + tickers_arr[num_ticker] + bcolors.OKCYAN + ", триггер: " + bcolors.ENDC + str(word_for_down))


                        if len(signal_action) < 3:
                            print(bcolors.OKCYAN + " " + tickers_arr[num_ticker] + ", маркеров для сигнала не найдено, отправляю в песочницу.")
                            query_sandbox = [(src_url, s[2][0:100], "Нет маркеров (" + tickers_arr[num_ticker] + ")")]
                            try:
                                cursor.executemany(
                                        "INSERT INTO sandbox (src, post, reason) VALUES (%s, %s, %s);", query_sandbox)
                            except Exception as ex:
                                print(bcolors.FAIL + '\n Не удалось добавить пост в песочницу :(\n', ex)
                        else:
                            print(bcolors.OKGREEN + ' Отправляю сигнал ' + bcolors.ENDC + tickers_arr[num_ticker] + ' ' + signal_action + bcolors.OKGREEN + ' всем подписчикам')
                            for user_name in members:
                                for value in user_name.values():
                                    if member_status[value]:
                                        send_signal(value, tickers_arr[num_ticker], signal_action, self.parse_subscribers_num(src_url))
                                    else:
                                        print(bcolors.ENDC + ' У @' + value + ' закончилась подписка')

                        num_ticker += 1

                    connect.commit()


                # query_sandbox = [(src_url, post_text, "Больше 1 тикера")]
                # try:
                #     with connection as connect:
                #         cursor.executemany(
                #                 "INSERT INTO sandbox (src, post, reason) VALUES (%s, %s, %s);", query_sandbox)
                #         connect.commit()
                # except Exception as ex:
                #     print(bcolors.FAIL + '\n Не удалось добавить пост в песочницу :(\n', ex)
            else:
                print(bcolors.OKCYAN + " Не найдено ни одного тикера, отправляю пост в песочницу")
                query_sandbox = [(src_url, post_text, "Нет тикеров")]
                try:
                    with connection as connect:
                        cursor.executemany(
                                "INSERT INTO sandbox (src, post, reason) VALUES (%s, %s, %s);", query_sandbox)
                        connect.commit()
                except Exception as ex:
                    print(bcolors.FAIL + '\n Не удалось добавить пост в песочницу :(\n', ex)


    def parsePage(self, url) -> str:
        # Here page for parse
        connection = pymysql.connect(**sockdata)
        cursor = connection.cursor()
        query_code = []
        # try:
        with request.urlopen(url) as file:
            src = file.read()
            soup = BeautifulSoup(src, "lxml")
            span_classe = soup.find("div", class_="PulsePost__wrapper_QkcQp")
            post_div = soup.find("div", class_="PulsePostBody__clickable_ygAE0").get_text()

            # Находит и вырезает post_id, сохраняет его в переменную
            find_post_id = re.compile(r'data-post-id="[^"]*"')
            post_id = find_post_id.findall(str(span_classe))
            post_id = post_id[0].partition('"')[2][:-1]
            
            
            # Пропускаем пост, если он уже отправлялся
            match str(post_id).strip('[]') in str(last_ids).strip('[]'):
                case True:
                    print(bcolors.ENDC + ' Не отправляем пост', post_id) # Если пост уже отправлялся ранее
                    
                case False:

                    posts_list: list = []
                    duplicate_post: bool = False
                    print(bcolors.ENDC + ' Проверяю пост на существование дублей…')
                    for post in posts:
                        if duplicate_post:
                            break
                        df1 = post['post'].splitlines()
                        df2 = post_div.splitlines()
                        result = set(df1) & set(df2)
                        if len(result) == len(df2):
                            duplicate_post = True
                            print(bcolors.WARNING + ' Такой пост уже есть в песочнице.')
                    if duplicate_post != True:
                        ticker_and_keywords: dict = {}
                        for ticker in tickers:
                            ticker_and_keywords[ticker['ticker']] = ticker['keywords'].split(',')

                        self.isTickerOrKeywords(ticker_and_keywords, post_div, url) # Отправляет текст поста в нижнем регистре в функцию парсинга поста на тикеры и ключи

            # Записываем ID последнего отправленного поста, из URL источника
            query_code.append((post_id, url))
            # print(query_code)

            # Тут мы записываем ID последнего пересланного поста
            try:
                with connection as connect:
                    cursor.executemany(
                            "UPDATE pages SET last_post_id = %s WHERE url = %s;", query_code)
                    connect.commit()
            except Exception as ex:
                print(bcolors.FAIL + '\n Ошибка! Не удалось обновить ID последнего поста из парсера (last post id from pages) :(\n', ex)


        # except Exception as ex:
        #     print(bcolors.FAIL + " \nОшибка! Не удалось распарсить источник :(\n", ex)


    def close(self):
        self.connection.close()


userbotTG.run(subscribe())
botTG.start()
cryptobot = Cryptobot()
cryptobot.reinit(sockdata)
idle()
botTG.stop()

# cryptobot.close()

##########################################################################
###                         with Love from Russia <3                   ###
###                                                                    ###
###                       by NorthernBlow and Pooh Pooh                ###
##########################################################################