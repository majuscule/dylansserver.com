#!/usr/bin/python

PROJECTS_DIRECTORY = '/home/dylan/docs/projects'

import os
import time
import MySQLdb as db
import ConfigParser

config = ConfigParser.RawConfigParser()
config.read('/etc/dylanstestserver.ini')
domain = config.get('database', 'domain')
user = config.get('database', 'user')
password = config.get('database', 'password').replace('"', '')
database = config.get('database', 'database')
cursor = db.connect(domain, user, password, database).cursor()

notes = os.listdir(PROJECTS_DIRECTORY)

sql = "SELECT title FROM projects"
cursor.execute(sql)
results = cursor.fetchall()
existing_titles = []
for row in results:
    existing_titles.append(row[0])

for note in notes:
    if (note[:1] == '.'): continue
    title = note[:note.index('.')]
    f = open(os.path.join(PROJECTS_DIRECTORY, note))
    if title in existing_titles: continue
    text = f.read()
    cursor.execute("INSERT INTO projects (title, text)\
                        VALUES (%s, %s)", (title, text))
