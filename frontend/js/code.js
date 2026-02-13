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

function addContact(e) {
  if (e) e.preventDefault();

  //Get information from contactModal;
  let contact_firstName = document.getElementById("firstName").value;
  let contact_lastName = document.getElementById("lastName").value;
  let contact_email = document.getElementById("email").value;
  let contact_phone = document.getElementById("phone").value;

  //Get saved userId
  const userData = JSON.parse(localStorage.getItem("userData") || "{}");
  userId = userData.id;

  let tmp = {
    userId: userId,
    firstName: contact_firstName,
    lastName: contact_lastName,
    phone: contact_phone,
    email: contact_email,
  };

  let jsonPayload = JSON.stringify(tmp);

  let url = urlBase + "/addContact";

  let xhr = new XMLHttpRequest();
  xhr.open("POST", url, true);
  xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");

  try {
    xhr.onreadystatechange = function () {
      console.log("XHR State: ", this.readyState, "Status", this.status);

      if (this.readyState == 4 && this.status == 200) {
        console.log("Response received: ", xhr.responseText);
        let jsonObject = JSON.parse(xhr.responseText);
        console.log("Parsed response:", jsonObject);

        let isSuccess = jsonObject.success;

        //If contact successfully added, add contact to dashboard
        if (isSuccess) {
          console.log("Contact Added!");
          getAllContacts();
        }
      }
    };
    xhr.send(jsonPayload);
  } catch (err) {
    console.log("ERROR");
  }
  closeModal();
}

let contactIdToDelete = -1;
let contactIdToEdit = -1;

function editContact(e) {
  if (e) e.preventDefault();

  const modal = document.getElementById("editModal");

  //these collects the updated fields from the edit modal
  let firstName = modal.querySelector("#editFirstName").value;
  let lastName = modal.querySelector("#editLastName").value;
  let email = modal.querySelector("#editEmail").value;
  let phone = modal.querySelector("#editPhone").value;

  //sets up payload
  let tmp = {
    contactId: contactIdToEdit,
    firstName: firstName,
    lastName: lastName,
    email: email,
    phone: phone,
  };

  //here, we call the api endpoint /updateContact with our payload. the logic is similar to deleteContact.
  let jsonPayload = JSON.stringify(tmp);
  let url = urlBase + "/updateContact";

  let xhr = new XMLHttpRequest();
  xhr.open("POST", url, true);
  xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");

  try {
    xhr.onreadystatechange = function () {
      if (this.readyState == 4 && this.status == 200) {
        let jsonObject = JSON.parse(xhr.responseText);

        //change this if we finally have a success msg </3
        if (jsonObject.success) {
          console.log("Contact updated successfully.");
          closeEditModal();
          getAllContacts();
        } else {
          alert("Error: " + jsonObject.error);
        }
      }
    };
    xhr.send(jsonPayload);
  } catch (err) {
    console.error("ERROR", err.message);
  }
}

function showEditModal(id, firstName, lastName, email, phone) {
  contactIdToEdit = id;
  const modal = document.getElementById("editModal");

  //fill out fields of the contact
  modal.querySelector("#editFirstName").value = firstName;
  modal.querySelector("#editLastName").value = lastName;
  modal.querySelector("#editEmail").value = email;
  modal.querySelector("#editPhone").value = phone;
  modal.querySelector("#editContactId").value = id;

  //show modal
  modal.classList.remove("hidden");
  modal.classList.add("flex");
}

function closeEditModal(e) {
  if (e) e.preventDefault();
  contactIdToEdit = -1;

  //hides modal
  const modal = document.getElementById("editModal");
  modal.classList.add("hidden");
  modal.classList.remove("flex");
}

function closeDeleteModal() {
  //resets id
  contactIdToDelete = -1;

  //hides modal
  const modal = document.getElementById("deleteModal");
  modal.classList.add("hidden");
  modal.classList.remove("flex");
}

function showDeleteModal(contactId) {
  contactIdToDelete = contactId;

  //shows modal
  const modal = document.getElementById("deleteModal");
  modal.classList.remove("hidden");
  modal.classList.add("flex");
}

function confirmDelete() {
  //only deletes if user pressed "confirmed"
  if (contactIdToDelete !== -1) {
    deleteContact(contactIdToDelete);
    closeDeleteModal();
  }
}

function deleteContact(contactId) {
  let tmp = { contactId: contactId };

  let jsonPayload = JSON.stringify(tmp);

  let url = urlBase + "/deleteContact";

  let xhr = new XMLHttpRequest();
  xhr.open("POST", url, true);
  xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");

  try {
    xhr.onreadystatechange = function () {
      if (this.readyState == 4 && this.status == 200) {
        let jsonObject = JSON.parse(xhr.responseText);

        let isSuccess = jsonObject.success;

        //contact has been sucessfully deleted!
        if (isSuccess) {
          console.log("Contact deleted successfully");

          //display the contacts in UI.
          getAllContacts();
        } else {
          alert("Error: " + jsonObject.error);
        }
      }
    };
    xhr.send(jsonPayload);
  } catch (err) {
    console.error("ERROR", err.message);
  }
}

function displayContacts(contactList) {
  const contactsContainer = document.getElementById("contacts");

  //clear container first
  contactsContainer.innerHTML = "";

  //then, add the contacts we fetch
  contactList.forEach((contact) => {
    const contactCard = document.createElement("div");

    contactCard.className =
      "h-fit flex p-4 rounded-2xl border border-[#E4EEFF] bg-[#F8FAFF] hover:border-[#054bb3] transition-all group";

    contactCard.id = contact.id;

    contactCard.innerHTML = `
      <div class="shrink-0 mr-4 w-12 h-12 rounded-full bg-[#054bb3] flex items-center justify-center text-white font-bold">
        ${contact.firstName.charAt(0)}${contact.lastName.charAt(0)}
      </div>
      <div class="flex-grow min-w-0">
      <div class ="flex">
        <p class="font-bold text-[#0F172A]">${contact.firstName} ${contact.lastName}</p>
        <div class="ml-auto flex gap-2">
          <button class="text-slate-400 hover:text-blue-500 transition-colors" onclick="showEditModal('${contact.id}', '${contact.firstName}', '${contact.lastName}', '${contact.email}', '${contact.phone}')">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
              <path fill="currentColor" d="M3 21v-4.25L16.2 3.575q.3-.275.663-.425t.762-.15t.775.15t.65.45L20.425 5q.3.275.438.65T21 6.4q0 .4-.137.763t-.438.662L7.25 21zM17.6 7.8L19 6.4L17.6 5l-1.4 1.4z" />
            </svg>
          </button>
          <button class="text-slate-400 hover:text-red-500 transition-colors" onclick="showDeleteModal('${contact.id}')">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
              <path fill="currentColor" d="M7 21q-.825 0-1.412-.587T5 19V6H4V4h5V3h6v1h5v2h-1v13q0 .825-.587 1.413T17 21zm2-4h2V8H9zm4 0h2V8h-2z" />
            </svg>
          </button>
        </div>
      </div>
        <div class="flex items-center gap-1">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24">
            <path fill="#64748b" d="M22 6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2zm-2 0l-8 5-8-5zm0 12H4V8l8 5l8-5z" />
          </svg>
          <p class="text-sm text-slate-500 truncate">${contact.email}</p>
        </div>

        <div class="flex items-center gap-1">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24">
            <path fill="#64748b" d="m19.23 15.26l-2.54-.29a1.99 1.99 0 0 0-1.64.57l-1.84 1.84a15.05 15.05 0 0 1-6.59-6.59l1.85-1.85c.43-.43.64-1.03.57-1.64l-.29-2.52a2 2 0 0 0-1.99-1.77H5.03c-1.13 0-2.07.94-2 2.07c.53 8.54 7.36 15.36 15.89 15.89c1.13.07 2.07-.87 2.07-2v-1.73c.01-1.01-.75-1.86-1.76-1.98" />
          </svg>
          <p class="text-sm text-slate-500">${contact.phone}</p>
        </div>

      </div>
    `;

    contactsContainer.appendChild(contactCard);
  });
}

function searchContacts(e) {
  //only if user presses enter
  if (e.key === "Enter") {
    let srch = document.getElementById("searchQuery").value;

    const userData = JSON.parse(localStorage.getItem("userData") || "{}");
    let userId = userData.id;

    //our payload
    let tmp = {
      userId: userId,
      search: srch,
    };

    let jsonPayload = JSON.stringify(tmp);

    let url = urlBase + "/searchContacts";

    let xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");

    try {
      xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
          let jsonObject = JSON.parse(xhr.responseText);

          // display our results into UI.
          if (jsonObject.results) {
            displayContacts(jsonObject.results);
          } else {
            //no results, just display no contacts found
            displayNoContactsFound();;
          }
        }
      };
      xhr.send(jsonPayload);
    } catch (err) {
      console.log("ERROR: " + err.message);
    }
  }
}

function getAllContacts() {
  const userData = JSON.parse(localStorage.getItem("userData") || "{}");
  userId = userData.id;

  let tmp = {
    userId: userId,
  };

  let jsonPayload = JSON.stringify(tmp);

  let url = urlBase + "/getContacts";

  let xhr = new XMLHttpRequest();
  xhr.open("POST", url, true);
  xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");

  try {
    xhr.onreadystatechange = function () {
      console.log("XHR State: ", this.readyState, "Status", this.status);

      if (this.readyState == 4 && this.status == 200) {
        console.log("Response received: ", xhr.responseText);
        let jsonObject = JSON.parse(xhr.responseText);
        console.log("Parsed response: ", jsonObject);

        if (jsonObject.results) {
          //display the contacts in the UI.
          displayContacts(jsonObject.results);
        } else if (jsonObject.error == "No Contacts Found") {
          //no results, display no contacts found
          displayNoContactsFound();
        }
      }
    };
    xhr.send(jsonPayload);
  } catch (err) {
    console.log("ERROR");
  }
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

function displayNoContactsFound() {
  const contactsContainer = document.getElementById("contacts");

  //Clear Container
  contactsContainer.innerHTML = "";

  // Display Text when no contacts
  contactsContainer.innerHTML = ` <div
  class="gap-1 absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 flex items-center h-fit"
  >
  <p class="text-gray-400">No Contacts Found</p>
  <img class="h-10 opacity-50" src="./sad-sonic.png" alt="Sad Sonic icon" />
  </div>`;
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

  if (mode === "add") {
    title.innerText = "Add Contact";
    document.getElementById("firstName").value = "";
    document.getElementById("lastName").value = "";
    document.getElementById("email").value = "";
    document.getElementById("phone").value = "";
  }
}

function closeModal(e) {
  if (e) e.preventDefault();
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
