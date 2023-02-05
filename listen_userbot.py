from bs4 import BeautifulSoup
import pymysql
from config import sockdata
import re
from pyrogram import Client, filters
from os import environ
from dotenv import load_dotenv
from os.path import join, dirname
import string
import time



members:        list = []
tickers:        list = []
tickers_pump:   dict = {}
stopwords:      list = []
wordsup:        list = []
wordsdown:      list = []
settings:       list = []
opt:            dict = {}

load_dotenv(join(dirname(__file__), '.env'))
botTG = Client('listen_userbot_bot', api_id=environ.get('API_ID'), api_hash=environ.get('API_HASH'), bot_token=environ.get('TOKENTG'))
userbotTG = Client('listen_userbot', api_id=environ.get('API_ID'), api_hash=environ.get('API_HASH'))
chars = re.escape(string.punctuation)

async def send_signal(user_name, ticker, signal_action, count_members = 0):
    async with botTG:
        print('Отправляю сигнал для ' + user_name)
        if tickers_pump[ticker]:
            pump_num = count_members // int(opt['pump_for_chan'])
            if pump_num > 10:
                pump_num = 10
            await botTG.send_message(user_name, "Сигнал: PUMP " + str(pump_num) + " из 10. " + ticker + " " + signal_action)
        else:
            await botTG.send_message(user_name, "Сигнал: " + ticker + " " + signal_action)


@userbotTG.on_message(filters.channel)
async def listen(client, message):

    print('Start update Data...')
    try:
        connection = pymysql.connect(**sockdata)
        cursor = connection.cursor()
        with connection as connect:
            cursor.execute(
                'SELECT user_name FROM members')
            members = cursor.fetchall()
            cursor.execute(
                'SELECT ticker,keywords,pump FROM tickers')
            tickers = cursor.fetchall()
            cursor.execute(
                'SELECT stopword FROM stopwords')
            stopwords = cursor.fetchall()
            cursor.execute(
                'SELECT word_for_up FROM wordsup')
            wordsup = cursor.fetchall()
            cursor.execute(
                'SELECT word_for_down FROM wordsdown')
            wordsdown = cursor.fetchall()
            cursor.execute(
                'SELECT * FROM settings')
            settings = cursor.fetchall()


        for ticker in tickers:
            if ticker['pump']:
                tickers_pump[ticker['ticker']] = 1
            else:
                tickers_pump[ticker['ticker']] = 0

        for pump in settings:
            opt[pump['name']] = pump['value']

    except Exception as ex:
        print('failed for fetchall from database :(', ex)


    stopwords_list: list = []
    for stopword in stopwords:
        for value in stopword.values():
            stopwords_list = stopwords_list + value.split(',')
    #stopwords_list = map(str.lower, stopwords_list)

    ticker_and_keywords: dict = {}
    for ticker in tickers:
        ticker_and_keywords[ticker['ticker']] = ticker['keywords'].split(',')

    wordsup_list: list = []
    for word_for_up in wordsup:
        for value in word_for_up.values():
            wordsup_list = wordsup_list + value.split(',')
    #wordsup_list = map(str.lower, wordsup_list)

    wordsdown_list: list = []
    for word_for_down in wordsdown:
        for value in word_for_down.values():
            wordsdown_list = wordsdown_list + value.split(',')
    #wordsdown_list = map(str.lower, wordsdown_list)

    print('Data updated.')

    post_clean_text = re.sub(r'['+chars+']', '', str(message.text).lower())
    sandbox = list(set(stopwords_list) & set(post_clean_text.split()))
    if sandbox:
        print('В песочницу! Стоп слово: ' + sandbox[0])
        query_sandbox = [(message.sender_chat.username, message.text, 'Стоп слово: ' + sandbox[0])]
        connection = pymysql.connect(**sockdata)
        cursor = connection.cursor()
        with connection as connect:
            cursor.executemany(
                "INSERT INTO sandbox (src, post, reason) VALUES (%s, %s, %s);", query_sandbox)
            connect.commit()
    else:
        ticker_name:    str = ''
        ticker_count:   int = 0
        signal_action:  str = ''
        for ticker in ticker_and_keywords:
            #print('>>>>>>>>>>>>>>>>>>>>   ', ticker)
            if ticker.lower() in post_clean_text:
                print("Найден тикер " + ticker)
                ticker_name = ticker
                ticker_count += 1

                for word_for_up in wordsup_list:
                    if word_for_up.lower() in post_clean_text:
                        signal_action = 'повышение'
                        print('Обнаружен сигнал на повышение для ' + ticker + ', триггер: ' + str(word_for_up))

                for word_for_down in wordsdown_list:
                    if word_for_down.lower() in post_clean_text:
                        signal_action = 'понижение'
                        print('Обнаружен сигнал на понижение для ' + ticker + ', триггер: ' + str(word_for_down))
            else:
                tmp = set(ticker_and_keywords[ticker]) & set(post_clean_text.split())
                #print('>>>>>>>>>>>>>>>>>>>>   ', tmp)
                if tmp:
                    print('Найдены совпадения по ' + ticker + ', парсим пост. ' + str(tmp))
                    ticker_name = ticker
                    ticker_count += 1

                    for word_for_up in wordsup_list:
                        if word_for_up.lower() in post_clean_text:
                            signal_action = 'повышение'
                            print('Обнаружен сигнал на повышение для ' + ticker + ', триггер: ' + str(word_for_up))

                    for word_for_down in wordsdown_list:
                        if word_for_down.lower() in post_clean_text:
                            signal_action = 'понижение'
                            print('Обнаружен сигнал на понижение для ' + ticker + ', триггер: ' + str(word_for_down))


        if ticker_count == 1 and len(signal_action) < 3:
            print('Маркеров для сигнала не найдено.' + ticker)
            query_sandbox = [(message.sender_chat.username, message.text, 'Нет маркеров (' + ticker_name + ')')]
            connection = pymysql.connect(**sockdata)
            cursor = connection.cursor()
            with connection as connect:
                cursor.executemany(
                    "INSERT INTO sandbox (src, post, reason) VALUES (%s, %s, %s);", query_sandbox)
                connect.commit()
        elif ticker_count == 1:
            print('ОТПРАВЛЯЮ СИГНАЛ!')
            count_members = await userbotTG.get_chat_members_count(message.sender_chat.username)
            for user_name in members:
                for value in user_name.values():
                    await send_signal(value, ticker_name, signal_action, count_members)
                    # botTG.run(send_signal(value, ticker_name, signal_action, count_members))
        elif ticker_count >= 2:
            print('Найдено больше 1 тикера в посте!')
            query_sandbox = [(message.sender_chat.username, message.text, 'Больше 1 тикера')]
            connection = pymysql.connect(**sockdata)
            cursor = connection.cursor()
            with connection as connect:
                cursor.executemany(
                    "INSERT INTO sandbox (src, post, reason) VALUES (%s, %s, %s);", query_sandbox)
                connect.commit()
        else:
            print('Совпадений по тикерам нет. В песочницу')
            if message.text is not None:
                query_sandbox = [(message.sender_chat.username, message.text, 'Нет тикеров')]
                connection = pymysql.connect(**sockdata)
                cursor = connection.cursor()
                with connection as connect:
                    cursor.executemany(
                        "INSERT INTO sandbox (src, post, reason) VALUES (%s, %s, %s);", query_sandbox)
                    connect.commit()
            else:
                print('В посте нет текста!')


userbotTG.run()
