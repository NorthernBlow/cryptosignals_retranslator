#! /usr/bin/python3

import requests
from bs4 import BeautifulSoup
import lxml
import os
from urllib import request
import pymysql
from config import host, user, password, db_name



try:
    connection = pymysql.connect(
        host=host,
        port=3306,
        user=user,
        password=password,
        database=db_name,
        cursorclass=pymysql.cursors.DictCursor
        )
    print("Подключилось к бд")

except Exception as ex:
    print("Подключение сброшено")
    print(ex)

    try:
        with connection.cursor() as cursor:
            select_all_raws = "SELECT * FROM 'channels'"
            cursor.execute(select_all_raws)
            print("выбрали все из бд")
            rows = cursor.fetchall()
            for row in rows:
                print(row)
    finally:
        connection.close()




url = 'https://www.tinkoff.ru/invest/social/profile/De_vint/'
with request.urlopen(url) as file:
    src = file.read()


soup = BeautifulSoup(src, "lxml")

span_classe = soup.find_all("span", class_="TickerWithTooltip__ticker_YdPIW")


for var in span_classe:
    for ticker in var:

        print(ticker.strip())