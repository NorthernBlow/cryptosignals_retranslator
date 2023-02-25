##########################################################################
###     Hi! This bot monitors the signals from the database,           ###
###     and send notify to all members!                                ###
##########################################################################
###                         For Developers Info:                       ###
###                                                                    ###
###     1. Use tab, not space                                          ###
###     2. Write comments for your code                                ###
##########################################################################
###                         listen_bot.py                              ###
###                                                                    ###
###     This file parse listen new subscribers and                     ###
###     activate token.                                                ###
##########################################################################


import pymysql
from config import sockdata
from config import bcolors
from pyrogram import Client, filters
from os import environ
from dotenv import load_dotenv
from os.path import join, dirname
import datetime




load_dotenv(join(dirname(__file__), '.env'))
botTG = Client('listen_bot', api_id=environ.get('API_ID'), api_hash=environ.get('API_HASH'), bot_token=environ.get('TOKENTG'))


#
#
# Bot listen new subsribers,
# cmd: /start <code>
@botTG.on_message(filters.command("start"))
async def save_user_id(client, message):
    promo_code = message.text.split(' ')
    if len(promo_code) == 2:
        connection = pymysql.connect(**sockdata)
        cursor = connection.cursor()
        with connection as connect:
            try:
                cursor.execute(
                    "SELECT * FROM tokens WHERE token = '" + promo_code[1] + "'")
                token = cursor.fetchall()
            except Exception as ex:
                print(bcolors.FAIL + ' \nОшибка! Не удалось прочитать токен из базы данных :(\n', ex)
        if token and token[0]['activation_num'] > 0 and token[0]['lost_date'] > datetime.datetime.now():
            connection = pymysql.connect(**sockdata)
            cursor = connection.cursor()
            with connection as connect:
                try:
                    activation_num = token[0]['activation_num'] - 1
                    cursor.execute(
                        "UPDATE tokens SET activation_num = " + str(activation_num) + " WHERE id = " + str(token[0]['id']) + ";")
                    cursor.execute(
                        "SELECT * FROM members WHERE user_id = '" + str(message.from_user.id) + "'")
                    member = cursor.fetchall()
                    if member:
                        query_members = [(token[0]['lost_date'], token[0]['id'], message.from_user.id)]
                        cursor.executemany(
                            "UPDATE members SET lost_date = %s, token_id = %s WHERE user_id = %s;", query_members)
                    else:
                        query_members = [(message.from_user.id, message.from_user.username, token[0]['id'], token[0]['lost_date'])]
                        cursor.executemany(
                            "INSERT INTO members (user_id, user_name, token_id, lost_date) VALUES (%s, %s, %s, %s);", query_members)
                    connect.commit()
                    await message.reply('✅ Ты подписался на рассылку @pooh_dev_bot!')
                except Exception as ex:
                    print(bcolors.FAIL + ' \nОшибка! Не удалось добавить подписчика в базу данных :(\n', ex, bcolors.ENDC)
        else:
            await message.reply('❌ Ты указал недействительный токен, обратись к @northernblow или @pooh2pooh для покупки.')
    else:
        await message.reply('❌ Ты не указал код приглашения,\nиспользуй `/start <код>`\n\nЕсли у тебя его нет, обратись к @northernblow или @pooh2pooh для покупки.')






@botTG.on_message(filters.new_chat_members)
async def welcome_message(client, message):
    welcome_msg = 'напиши /start для продолжения'
    await message.reply_text('Привет, ты подписался на рассылку Cryptobot! \n%s' % (welcome_msg), disable_web_page_preview=True)





botTG.run()
