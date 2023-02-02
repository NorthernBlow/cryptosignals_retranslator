import pymysql
from config import sockdata
from pyrogram import Client, filters
from os import environ
from dotenv import load_dotenv
from os.path import join, dirname




load_dotenv(join(dirname(__file__), '.env'))
botTG = Client('listen_bot', api_id=environ.get('API_ID'), api_hash=environ.get('API_HASH'), bot_token=environ.get('TOKENTG'))



@botTG.on_message(filters.text & filters.private)
async def save_user_id(client, message):
    #print(message.from_user.id)
    if message.text == '/start':
        connection = pymysql.connect(**sockdata)
        cursor = connection.cursor()
        with connection as connect:
            try:
                query_members = [(message.from_user.id, message.from_user.username)]
                cursor.executemany(
                    "INSERT INTO members (user_id, user_name) VALUES (%s, %s);", query_members)
                connect.commit()
                await message.reply('✅ Ты подписался на рассылку @PumpEffectBot!')
            except Exception as ex:
                print('failed add user_id to database :(', ex)

botTG.run()
