import sqlite3
import csv

conn = sqlite3.connect("database/database.sqlite")
cursor = conn.cursor()

cursor.execute("SELECT * FROM log_monitor")

with open("log_monitor.csv", "w", newline="") as f:
    writer = csv.writer(f)
    try:
        writer.writerow([description[0] for description in cursor.description])
        writer.writerows(cursor.fetchall())
    except Exception as e:
        print(f"Error writing to CSV: {e}")

conn.close()