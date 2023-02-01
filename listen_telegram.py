from bs4 import BeautifulSoup
import pymysql
from config import sockdata
import re
from pyrogram import Client, filters
from os import environ
from dotenv import load_dotenv
from os.path import join, dirname
import string



tickers:    list = []
stopwords:  list = []
wordsup:    list = []
wordsdown:  list = []

load_dotenv(join(dirname(__file__), '.env'))
botTG = Client(environ.get('APP_NAME'), api_id=environ.get('API_ID'), api_hash=environ.get('API_HASH'))
chars = re.escape(string.punctuation)


params = {
        "source_chat_id": -1001075101206,
        "target_chat_id": -1001789873317,
}

try:
    connection = pymysql.connect(**sockdata)
    cursor = connection.cursor()
    with connection as connect:
        cursor.execute(
            'SELECT ticker,keywords FROM tickers')
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

#except Exception as ex:
#    print('failed for fetchall from database :(', ex)

except pymsql.err.OperationalError:
    print("cant fetchall from database :(")
    if connection.is_connected():
        connection.close()

finally:
    time.sleep(4)



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

@botTG.on_message(filters.chat('ochen_normalno'))
async def listen(client, message):
    #print(message.sender_chat.username)
    post_clean_text = re.sub(r'['+chars+']', '', str(message.text.lower()))
    sandbox = set(stopwords_list) & set(post_clean_text.split())
    if sandbox:
        print('В песочницу! Стоп слово: ' + str(sandbox))
        query_sandbox = [('telegram', message.text, 'Стоп слово: ' + str(sandbox))]
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
            await botTG.send_message(params['target_chat_id'], 'Сигнал: ' + ticker_name + ' ' + signal_action)
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
            print('Совпадений нет')
            query_sandbox = [(message.sender_chat.username, message.text, 'Нет тикеров')]
            connection = pymysql.connect(**sockdata)
            cursor = connection.cursor()
            with connection as connect:
                cursor.executemany(
                    "INSERT INTO sandbox (src, post, reason) VALUES (%s, %s, %s);", query_sandbox)
                connect.commit()


botTG.run()
