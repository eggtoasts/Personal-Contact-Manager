import requests

url = "https://personal-contact-manager-production.up.railway.app/Login.php"

data = {
    "email": "sonic@greenhill.com",
    "password": "hotdog"
}

response = requests.post(url, data=data)
print(response.status_code)
