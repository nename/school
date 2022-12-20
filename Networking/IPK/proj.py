import socket
import sys
import json

# check if there is enough arguments to work with
if (len(sys.argv) != 3): 
	sys.stderr.write("Wrong number of arguments!\n")
	exit(1)

api = sys.argv[1]
city = sys.argv[2]

s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
server = "api.openweathermap.org"
port = 80

request = "GET /data/2.5/weather?q=" + city + "&units=metric&APPID=" + api + " HTTP/1.0\r\n\r\n"

# connecting to server and sending request
s.connect((server, port)) 
s.send(request.encode())

# converting data from bytes to string
data = s.recv(4096).decode("utf-8") 

# converting to json format
data = json.loads("{" + data.split('{' ,1)[1]) 

if (not("cod" in data)):
	sys.stderr.write("Wrong data received\n")
	exit(1)	

# check if api key was correct and city was found
if (data['cod'] != 200): 
	sys.stderr.write(data['message'])
	exit(1)

print("city: " + data['name'])
print("overcast: " + data['weather'][0]['main'])
print("temp: " + str(data['main']['temp']) + " °C")
print("humidity: " + str(data['main']['humidity']) + " %")
print("pressure: " + str(data['main']['pressure']) + " hPa")
# api returns m/s so we have to convert it to km/h
print("wind-speed: " + str("{0:.2f}".format((data['wind']['speed'])*3.6)) + " km/h") 
(print("wind-deg: n/a")) if (data['wind']['deg'] is None) else (print("wind-deg: " + str(data['wind']['deg'])))