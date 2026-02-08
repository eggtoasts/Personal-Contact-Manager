import requests

url = "https://personal-contact-manager-production.up.railway.app/Login.php"

data = {
    "login": "sonic@greenhill.com",
    "password": "hotdog"
}

response = requests.post(url, json=data)
print(response.status_code, response.text)
