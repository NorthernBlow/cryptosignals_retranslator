import requests
from bs4 import BeautifulSoup as bs
import lxml
import os
from urllib import request


url = 'https://www.tinkoff.ru/invest/social/profile/De_vint/'
with request.urlopen(url) as file:
    src = file.read()


soup = bs(src, "lxml")

span_classe = soup.find_all("span", class_="TickerWithTooltip__ticker_YdPIW")


for var in span_classe:
    for ticker in var:

        print(ticker.strip())