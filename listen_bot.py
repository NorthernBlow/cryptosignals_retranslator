import pymysql
from config import sockdata
from pyrogram import Client, filters
from os import environ
from dotenv import load_dotenv
from os.path import join, dirname




load_dotenv(join(dirname(__file__), '.env'))
botTG = Client('listen_bot', api_id=environ.get('API_ID'), api_hash=environ.get('API_HASH'), bot_token=environ.get('TOKENTG'))



@botTG.on_message(filters.command("start"))
async def save_user_id(client, message):
    promo_code = message.text.split(' ')
    if len(promo_code) == 2:
        connection = pymysql.connect(**sockdata)
        cursor = connection.cursor()
        with connection as connect:
            try:
                cursor.execute(
                    "SELECT username FROM users WHERE token = '" + promo_code[1] + "'")
                member = cursor.fetchall()
            except Exception as ex:
                print('failed select token from database :(', ex)
        if member:
            connection = pymysql.connect(**sockdata)
            cursor = connection.cursor()
            with connection as connect:
                try:
                    query_members = [(message.from_user.id, message.from_user.username)]
                    cursor.executemany(
                        "INSERT INTO members (user_id, user_name) VALUES (%s, %s);", query_members)
                    connect.commit()
                    await message.reply('✅ Ты подписался на рассылку @pooh_dev_bot!')
                except Exception as ex:
                    print('failed add user_id to database :(', ex)
        else:
            await message.reply('❌ Ты указал не действительный токен, обратись к @northernblow или @pooh2pooh для покупки.')
    else:
        await message.reply('❌ Ты не указал код приглашения,\nиспользуй `/start <код>`\n\nЕсли у тебя его нет, обратись к @northernblow или @pooh2pooh для покупки.')
botTG.run()
