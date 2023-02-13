##########################################################################
###     Hi! This bot monitors the signals from the database,           ###
###     and send notify to all members!                                ###
##########################################################################
###                         For Developers Info:                       ###
###                                                                    ###
###     1. Use tab, not space                                          ###
###     2. Write comments for your code                                ###
##########################################################################
###                         listen_userbot.py                          ###
###                                                                    ###
###     This file parse listen new subscribers and                     ###
###     activate token.                                                ###
##########################################################################


from bs4 import BeautifulSoup
import pymysql
from config import sockdata
from config import bcolors
import re
from pyrogram import Client, filters
from os import environ
from dotenv import load_dotenv
from os.path import join, dirname
import string
import time
import datetime



members:        list = []
member_status:  dict = {}
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
# chars = re.escape(string.punctuation)

async def send_signal(user_name: str, ticker: str, signal_action: str, count_members: int = 0):
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
    async with botTG:
        print(bcolors.OKBLUE + ' Отправляю сигнал для ' + bcolors.ENDC + '@' + user_name)
        pump_signal = ''
        if tickers_pump[ticker]:
            pump_num = count_members // int(opt['pump_for_page'])
            if pump_num > 10:
                pump_num = 10
            if pump_num > 0:
                pump_signal = 'PUMP ' + str(pump_num) + " из 10."
        await botTG.send_message(user_name, "Сигнал: " + pump_signal + " " + ticker + " " + signal_action)
        time.sleep(0.4)


async def get_signal_action(post_text, ticker, src_url):

    connection = pymysql.connect(**sockdata)
    cursor = connection.cursor()

    signal_action = ''

    with connection as connect:
        cursor.execute(
            'SELECT user_name FROM members')
        members = cursor.fetchall()
        cursor.execute(
            "SELECT user_name,status FROM members")
        tmp = cursor.fetchall()
        for member in tmp:
            if member['status']:
                member_status[member['user_name']] = 1
            else:
                member_status[member['user_name']] = 0
        cursor.execute(
            'SELECT word_for_up FROM wordsup')
        wordsup = cursor.fetchall()
        cursor.execute(
            'SELECT word_for_down FROM wordsdown')
        wordsdown = cursor.fetchall()

        wordsup_list: list = []
        for word_for_up in wordsup:
            for value in word_for_up.values():
                wordsup_list = wordsup_list + value.split(',')
        wordsup_list = map(str.lower, wordsup_list)

        wordsdown_list: list = []
        for word_for_down in wordsdown:
            for value in word_for_down.values():
                wordsdown_list = wordsdown_list + value.split(',')
        wordsdown_list = map(str.lower, wordsdown_list)

        # Ищем слова из словаря на повышение
        #
        for word_for_up in wordsup_list:
            if word_for_up.lower() in post_text:
                signal_action = 'повышение'
                print('Обнаружен сигнал на повышение для ' + ticker + ', триггер: ' + str(word_for_up))

        if len(signal_action) < 3:
            # Ищем слова из словаря на понижение
            #
            for word_for_down in wordsdown_list:
                if word_for_down.lower() in post_text:
                    signal_action = 'понижение'
                    print('Обнаружен сигнал на понижение для ' + ticker + ', триггер: ' + str(word_for_down))

        if len(signal_action) < 3:
            # Дальше идёт код который срабатывает в случае,
            # когда никаких сигналов в посте не найдено
            #
            print(bcolors.OKCYAN + " " + ticker + ", маркеров для сигнала не найдено, отправляю в песочницу.")
            query_sandbox = [(src_url, post_text, 'Нет маркеров (' + ticker + ')')]
            cursor.executemany(
                "INSERT INTO sandbox (src, post, reason) VALUES (%s, %s, %s);", query_sandbox)
            connect.commit()
        else:
            print(bcolors.OKGREEN + ' Отправляю сигнал ' + bcolors.ENDC + ticker + ' ' + signal_action + bcolors.OKGREEN + ' всем подписчикам')
            count_members = await userbotTG.get_chat_members_count(src_url)
            for user_name in members:
                for value in user_name.values():
                    if member_status[value]:
                        await send_signal(value, ticker, signal_action, count_members)
                    else:
                        print(bcolors.ENDC + ' У @' + value + ' закончилась подписка')


@userbotTG.on_message(filters.channel)
async def listen(client, message):

    connection = pymysql.connect(**sockdata)
    cursor = connection.cursor()

    with connection as connect:
        try:
            cursor.execute(
                'SELECT ticker,keywords,pump FROM tickers')
            tickers = cursor.fetchall()
            cursor.execute(
                'SELECT stopword FROM stopwords')
            stopwords = cursor.fetchall()
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

            now = datetime.datetime.now();
            print(bcolors.ENDC, now.strftime('%d %b %H:%M (%a)'), bcolors.OKGREEN + ' Данные успешно загружены из базы' + bcolors.ENDC + ' OK')

        except Exception as ex:
            print(bcolors.FAIL + '\n Ошибка! Не удалось соединиться с базой данных или другая системная ошибка, извини, точнее не скажу :(\n', ex)


        stopwords_list: list = []
        for stopword in stopwords:
            for value in stopword.values():
                stopwords_list = stopwords_list + value.split(',')
        stopwords_list = map(str.lower, stopwords_list)

        ticker_and_keywords: dict = {}
        for ticker in tickers:
            ticker_and_keywords[ticker['ticker']] = ticker['keywords'].lower().split(',')


        # post_clean_text = re.sub(r'['+chars+']', '', str(message.text).lower())
        post_clean_text = str(message.text).lower()

        # Парсим стоп слова в тексте поста
        #
        sandbox = list(set(stopwords_list) & set(post_clean_text.split()))

        if sandbox:
            print(bcolors.OKCYAN + " Найдено стоп слово: " + sandbox[0])
            query_sandbox = [(message.sender_chat.username, message.text, 'Стоп слово: ' + sandbox[0])]
            cursor.executemany(
                "INSERT INTO sandbox (src, post, reason) VALUES (%s, %s, %s);", query_sandbox)
            print(bcolors.OKCYAN + ' Добавил пост в песочницу')

        else:
            ticker_name:    str = ''
            tickers_arr:    dict = {}
            ticker_count:   int = 0
            signal_action:  str = ''

            for ticker in ticker_and_keywords:

                prepare_ticker = ticker.lower()
                prepare_ticker = ['$' + prepare_ticker + ' ', '$' + prepare_ticker + ',', '$' + prepare_ticker + ':', '$' + prepare_ticker + '.', '$' + prepare_ticker + '\n']

                for curr_ticker in prepare_ticker:
                    if curr_ticker in post_clean_text or list(set(ticker_and_keywords[ticker]) & set(post_clean_text.split())):
                        print(bcolors.OKCYAN + " Найден тикер " + ticker + "…") # или ключевое слово

                        ticker_name = ticker
                        ticker_count += 1

                        tickers_arr[ticker] = ticker_and_keywords[ticker]
                        break


            if ticker_count == 1:

                try:
                    await get_signal_action(result, ticker, message.sender_chat.username)
                except Exception as ex:
                    print(bcolors.FAIL + ' Ошибка! Не могу обработать пост :(\n', ex)

            elif ticker_count > 1:
                print(bcolors.OKCYAN + " Найдено больше 1 тикера в посте!")

                for ticker in tickers_arr:

                    isKeyword = list(set(ticker_and_keywords[ticker]) & set(post_clean_text.split()))

                    if '$' + ticker.lower() in post_clean_text:
                        target = post_clean_text.partition(ticker.lower())
                    elif len(isKeyword) > 0:
                        target = post_clean_text.partition(isKeyword[0])

                    result = target[1] + target[2]

                    if len(result) > 100:
                        print(bcolors.ENDC + ' Обрезаю пост до 100 символов…')
                        result = result[0:100]

                    if '\n\n' in result:
                        print(bcolors.ENDC + ' Обрезаю пост до двух переносов строки…')
                        result = result.partition('\n\n')[0]

                    for next_ticker in tickers_arr:
                        if ticker == next_ticker or list(set(ticker_and_keywords[ticker]) & set(ticker_and_keywords[next_ticker])):
                            continue
                        isKeyword = list(set(ticker_and_keywords[next_ticker]) & set(result.split()))

                        if next_ticker in result:
                            print(bcolors.ENDC + ' Обрезаю пост до упоминания следующего тикера…')
                            result = result.partition(next_ticker)[0]
                        elif len(isKeyword) > 0:
                            print(bcolors.ENDC + ' Обрезаю пост до упоминания следующего тикера (ключа)…')
                            result = result.partition(isKeyword[0])[0]

                    try:
                        await get_signal_action(result, ticker, message.sender_chat.username)
                    except Exception as ex:
                        print(bcolors.FAIL + ' Ошибка! Не могу обработать пост :(\n', ex)

            else:
                print(' Совпадений по тикерам нет. В песочницу')
                if message.text is not None:
                    query_sandbox = [(message.sender_chat.username, message.text, 'Нет тикеров')]
                    cursor.executemany(
                        "INSERT INTO sandbox (src, post, reason) VALUES (%s, %s, %s);", query_sandbox)
                else:
                    print(' В посте нет текста!')

        connect.commit()


userbotTG.run()
