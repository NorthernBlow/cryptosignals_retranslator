from pyrogram import Client
from os import environ
from dotenv import load_dotenv
from os.path import join, dirname
import portion
from bs4 import BeautifulSoup
import lxml
from urllib import request
import lxml
import re


load_dotenv(join(dirname(__file__), '.env'))



botTG = Client(environ.get('APP_NAME'), api_id=environ.get('API_ID'), api_hash=environ.get('API_HASH'))



#тестовые данные, чтобы получать count подписчиков
tgchannel = {
    "source_chat_id": -1001821693693,
    "target_chat_id": -1001789873317,
}
count: int = 0
#переопределил число подписчиков на тестовое
count: int = 756664



#диапазоны чисел
range1 = portion.open(2000, 3999)
range2 = portion.open(4000, 7999)
range3 = portion.open(8000, 12999)
range4 = portion.open(13000, 20999)
range5 = portion.open(21000, 32999)
range6 = portion.open(71000, 142999)
range7 = portion.open(143000, 286999)
range8 = portion.open(287000, 500999)
range9 = portion.open(500100, 700999)
range10 = portion.open(701000, 1000001)


PUMP: dict = {
	
	'pump 1/10': range1,
	'pump 2/10': range2,
	'pump 3/10': range3,
	'pump 4/10': range4,
	'pump 5/10': range5,
	'pump 6/10': range6,
	'pump 7/10': range7,
	'pump 8/10': range8,
	'pump 9/10': range9,
	'pump 10/10': range10,
}

def check_subs_for_pump(count: int, pump: dict) -> str:
	"""Функция для получения вхождений числа подписчиков в диапазон чисел,
	диапазон чисел определен ключами словаря PUMP, который она принимает на вход
	так же, как и число подписчиков. см ниже:

args: count - число подписчиков в каждой группе типа integer
pump == PUMP - словарь с диапазоном чисел в значениях ключей.

"""
	for pump, subscribers in PUMP.items():
		if count in subscribers:
			print("это памп ->", pump)
			return pump #возвращает совпадение из диапазона чисел



async def get_members(tgchannel: dict) -> int:
	"""Функция для получения подписчиков из телеграм каналов

args: tgchannel - число подписчиков в каждой группе типа integer. См def readtelegram() в main.py
get_chat_members_count() - принимает chat id, функцию надо разместить в цикле и раздавать ей айдишники.
return: возвращает число подписчиков в группе типа integer

"""
	async with botTG:
		count = await botTG.get_chat_members_count(tgchannel['target_chat_id']) #сюда надо в параметры закидывать в цикле
		print("это число подписчиков в телеграм ->", count)
		return count #возвращает число подписчиков в группе типа integer





#тестовый url, он в мейне из цикла берется на 345стр.
url: str = 'https://www.tinkoff.ru/invest/social/profile/Vasiliy_Oleynik/'


def parse_subscribers(url: str) -> int:
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
	    subscr_div = int(subscr_div)	# типа integer 94485
	    print("это число подписчиков в тинькофф ->", subscr_div) 
	    return subscr_div





botTG.run(get_members(tgchannel))
check_subs_for_pump(count, PUMP)
parse_subscribers(url)


