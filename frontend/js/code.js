// change below when we have a domain set up
const urlBase = "https://personal-contact-manager-production.up.railway.app"; // Replace with your actual Railway backend URL
const frontendBase = window.location.origin; // Gets current frontend URL automatically
const extension = "";

let userId = 0;
let firstName = "";
let lastName = "";

function Register() {
  //Get informatin user entered into Register.html
  firstName = document.getElementById("firstName").value;
  lastName = document.getElementById("lastName").value;
  let login = document.getElementById("loginName").value;
  let password = document.getElementById("loginPassword").value;

  console.log("User info:", firstName, " ", lastName, " ", login);

  let tmp = {
    firstName: firstName,
    lastName: lastName,
    login: login,
    password: password,
  };

  let jsonPayload = JSON.stringify(tmp);

  let url = urlBase + "/Register";

  let xhr = new XMLHttpRequest();
  xhr.open("POST", url, true);
  xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");

  try {
    xhr.onreadystatechange = function () {
      console.log("XHR State: ", this.readyState, "Status: ", this.status);

      if (this.readyState == 4 && this.status == 200) {
        console.log("Reponse received: ", xhr.responseText);
        let jsonObject = JSON.parse(xhr.responseText);
        console.log("Parsed response:", jsonObject);

        userId = jsonObject.id;

        if (userId < 1) {
          document.getElementById("loginResult").innerHTML =
            "Registration Unsuccessful";
          return;
        } else {
          document.getElementById("loginResult").innerHTML =
            "Registration Complete";
        }

        firstName = jsonObject.firstName;
        lastName = jsonObject.lastName;
        login = jsonObject.login;

        console.log(
          "Registration successful, user:",
          firstName,
          lastName,
          login,
        );
      }
    };
    xhr.send(jsonPayload);
  } catch (err) {
    document.getElementById("loginResult").innerHTML = err.message;
  }
}

function doLogin() {
  try {
    userId = 0;
    firstName = "";
    lastName = "";

    let login = document.getElementById("loginName").value;
    let password = document.getElementById("loginPassword").value;
    //	var hash = md5( password );

    document.getElementById("loginResult").innerHTML = "";

    let tmp = { login: login, password: password };
    //	var tmp = {login:login,password:hash};
    let jsonPayload = JSON.stringify(tmp);

    let url = urlBase + "/Login";

    let xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");

    xhr.onreadystatechange = function () {
      console.log("XHR State:", this.readyState, "Status:", this.status);

      if (this.readyState == 4) {
        if (this.status == 200) {
          try {
            console.log("Response received:", xhr.responseText);
            let jsonObject = JSON.parse(xhr.responseText);
            console.log("Parsed response:", jsonObject);

            userId = jsonObject.id;

            if (userId < 1) {
              document.getElementById("loginResult").innerHTML =
                "User/Password combination incorrect";
              return;
            }

            firstName = jsonObject.firstName;
            lastName = jsonObject.lastName;

            console.log(
              "Login successful, user:",
              firstName,
              lastName,
              "ID:",
              userId,
            );

            // Save user data to localStorage for color.html page
            const userData = {
              id: userId,
              firstName: firstName,
              lastName: lastName,
              success: jsonObject.success,
              timestamp: jsonObject.timestamp,
            };
            localStorage.setItem("userData", JSON.stringify(userData));
            localStorage.setItem("loginTime", new Date().toISOString());

            console.log("User data saved to localStorage");

            saveCookie();

            console.log("Redirecting to dashboard.html...");
            window.location.href = "./dashboard.html";
          } catch (parseErr) {
            console.error("JSON parse error:", parseErr);
            document.getElementById("loginResult").innerHTML =
              "Invalid response from server";
          }
        } else {
          console.error("HTTP Error:", this.status, xhr.responseText);
          document.getElementById("loginResult").innerHTML =
            "Login failed. Please try again.";
        }
      }
    };
    xhr.send(jsonPayload);
  } catch (err) {
    console.error("Login function error:", err);
    document.getElementById("loginResult").innerHTML =
      "Login error: " + err.message;
  }
}

function addContact() {
  const modal = document.getElementById("contactModal");

  let contact_firstName = modal.getElementById("firstName").value;
  let contact_lastName = modal.getElementById("lastName").value;
  let contact_email = modal.getElementsById("email").value;
  let contact_phone = modal.getElementByID("phone").value;

  console.log("New Contact info",
    " firstname: ", contact_firstName,
    " lastname: ", contact_lastName,
    " email: ", contact_email,
    " phone: ", contact_phone,
  );

  let tmp = {
    userId:userId,
    firstName:contact_firstName,
    lastname:contact_lastName,
    phone:contact_phone,
    email:contact_email
  };

  let jsonPayload = JSON.stringify(tmp);

  let url = urlBase + "/addContact";

}

function editContact() {}

function deleteContact() {}

function getContacts() {
  const contactsContainer = document.getElementById("contacts");

  //clear container first

  const child = contactId.lastElementChild;
  while (child) {
    contactsContainer.removeChild(child);
    child = child.lastElementChild;
  }

  //then, add the contacts we fetch
}

function saveCookie() {
  let minutes = 20;
  let date = new Date();
  date.setTime(date.getTime() + minutes * 60 * 1000);
  document.cookie =
    "firstName=" +
    firstName +
    ",lastName=" +
    lastName +
    ",userId=" +
    userId +
    ";expires=" +
    date.toGMTString();
}

function readCookie() {
  userId = -1;
  let data = document.cookie;
  let splits = data.split(",");
  for (var i = 0; i < splits.length; i++) {
    let thisOne = splits[i].trim();
    let tokens = thisOne.split("=");
    if (tokens[0] == "firstName") {
      firstName = tokens[1];
    } else if (tokens[0] == "lastName") {
      lastName = tokens[1];
    } else if (tokens[0] == "userId") {
      userId = parseInt(tokens[1].trim());
    }
  }

  if (userId < 0) {
    window.location.href = "index.html";
  } else {
    //		document.getElementById("userName").innerHTML = "Logged in as " + firstName + " " + lastName;
  }
}

function doLogout() {
  userId = 0;
  firstName = "";
  lastName = "";
  document.cookie = "firstName= ; expires = Thu, 01 Jan 1970 00:00:00 GMT";
  window.location.href = "index.html";
}

function addColor() {
  let newColor = document.getElementById("colorText").value;
  document.getElementById("colorAddResult").innerHTML = "";

  let tmp = { color: newColor, userId, userId };
  let jsonPayload = JSON.stringify(tmp);

  let url = urlBase + "/AddColor." + extension;

  let xhr = new XMLHttpRequest();
  xhr.open("POST", url, true);
  xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
  try {
    xhr.onreadystatechange = function () {
      if (this.readyState == 4 && this.status == 200) {
        document.getElementById("colorAddResult").innerHTML =
          "Color has been added";
      }
    };
    xhr.send(jsonPayload);
  } catch (err) {
    document.getElementById("colorAddResult").innerHTML = err.message;
  }
}

function searchColor() {
  let srch = document.getElementById("searchText").value;
  document.getElementById("colorSearchResult").innerHTML = "";

  let colorList = "";

  let tmp = { search: srch, userId: userId };
  let jsonPayload = JSON.stringify(tmp);

  let url = urlBase + "/SearchColors." + extension;

  let xhr = new XMLHttpRequest();
  xhr.open("POST", url, true);
  xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
  try {
    xhr.onreadystatechange = function () {
      if (this.readyState == 4 && this.status == 200) {
        document.getElementById("colorSearchResult").innerHTML =
          "Color(s) has been retrieved";
        let jsonObject = JSON.parse(xhr.responseText);

        for (let i = 0; i < jsonObject.results.length; i++) {
          colorList += jsonObject.results[i];
          if (i < jsonObject.results.length - 1) {
            colorList += "<br />\r\n";
          }
        }

        document.getElementsByTagName("p")[0].innerHTML = colorList;
      }
    };
    xhr.send(jsonPayload);
  } catch (err) {
    document.getElementById("colorSearchResult").innerHTML = err.message;
  }
}

function togglePasswordVisibility() {
  const passwordInput = document.getElementById("loginPassword");
  const toggleText = document.getElementById("toggleText");

  if (passwordInput.type === "password") {
    passwordInput.type = "text";
    toggleText.textContent = "Hide";
  } else {
    passwordInput.type = "password";
    toggleText.textContent = "Show";
  }
}

function openModal(mode) {
  const modal = document.getElementById("contactModal");
  const title = document.getElementById("modalTitle");

  //resets data for every reopen
  modal.classList.remove("hidden");
  modal.classList.add("flex");

  //add a "edit" mode later
  if (mode === "add") {
    currentContactId = -1;
    title.innerText = "Add Contact";
    document.getElementById("firstName").value = "";
    document.getElementById("lastName").value = "";
    document.getElementById("email").value = "";
    document.getElementById("phone").value = "";
  }
}

function closeModal() {
  const modal = document.getElementById("contactModal");
  modal.classList.add("hidden");
  modal.classList.remove("flex");
}

//close modal if clicking outside
window.onclick = function (event) {
  const modal = document.getElementById("contactModal");
  if (event.target == modal) {
    closeModal();
  }
};
