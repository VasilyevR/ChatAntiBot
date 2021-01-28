# ChatAntiBot

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/91d7800b7d8a429bb46c2b0c17ec5075)](https://app.codacy.com/gh/VasilyevR/ChatAntiBot?utm_source=github.com&utm_medium=referral&utm_content=VasilyevR/ChatAntiBot&utm_campaign=Badge_Grade)
[![SymfonyInsight](https://insight.symfony.com/projects/c6ac2476-8135-4dd1-be5b-7379259a5111/mini.svg)](https://insight.symfony.com/projects/c6ac2476-8135-4dd1-be5b-7379259a5111)

Spammer kicker Telegram Bot for checking spammer bots with questions.

## Download

```sh
git clone https://github.com/VasilyevR/ChatAntiBot.git
cd ChatAntiBot
cp config/parameters_sample.php config/parameters.php
cp config/riddles_sample.php config/riddles.php
```
Edit config/parameters.php with your ***BOT_API_KEY*** and config/riddles.php with your riddles if needs.

## Build

```sh
docker build -f dockerfiles/php-fpm/Dockerfile -t imagefile .
```

## Run

```sh
docker run -it imagefile
```
