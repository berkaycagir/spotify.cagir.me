#!/usr/bin/python
import requests, pickle, smtplib
from email.mime.text import MIMEText
from checker_config import *

def get_access_token():
    spotifyData = {'grant_type': 'refresh_token', 'refresh_token': SPOTIFY_REFRESH_TOKEN}
    spotifyHeaders = {"Authorization": "Basic " + SPOTIFY_AUTH_TOKEN}
    r = requests.post("https://accounts.spotify.com/api/token", data=spotifyData, headers=spotifyHeaders)
    spotifyData = r.json()
    accessToken = spotifyData['access_token']
    spotifyHeaders = {"Authorization": "Bearer " + accessToken, "Accept": "application/json"}
    return spotifyHeaders

def load_list():
    iof = open(PATH + "/list.p", "rb")
    input_list = pickle.load(iof)
    iof.close()
    return input_list

def save_list(input_list):
    iof = open(PATH + "/list.p", "wb")
    pickle.dump(input_list, iof, -1)
    iof.close()

def send_mail(body, subject, recipient):
    msg = MIMEText(body, "plain", "utf-8")
    msg['Subject'] = subject
    msg['From'] = 'Spotify Song Availability Checker <checker@cagir.me>'
    msg['To'] = recipient
    s = smtplib.SMTP(MAIL_HOST, MAIL_PORT)
    s.login(MAIL_ADDR, MAIL_PASSWD)
    s.sendmail(MAIL_ADDR, [recipient], msg.as_string())
    s.quit()
