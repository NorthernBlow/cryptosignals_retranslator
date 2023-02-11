# cryptosignals_retranslator
telegram bot that rebroadcasts crypto signals from bloggers on the web and telegram channels. it has a web admin panel as a graphical interface

<img src="https://github.com/NorthernBlow/cryptosignals_retranslator/tree/main/screenshots/admin.png" alt="admin-panel"  />


# Installation:
1. `git clone`
2. edit `config.py` for database connect param
3. make `admin-panel/token.php` and write `<?php $tg_bot_token = "<your_token>";`
4. `mv admin_panel` to `www` or `htdocs` directory your web server
5. say *«Dear Svyatoslav!...»*, or *«Great Andrey!...»*, *«Please, Give Me Database admin_panel.sql»*
6. Wait...


# Run:
1. `mv services/* /etc/systemd/system/`
2. `systemctl start cb_listen.service` — for start bot listen telegram channels
3. `systemctl start cryptobot.service` — for start bot listen new subscribers
4. `systemctl start cb_parser.service` — for start bot parser fuck tinkoff pages
