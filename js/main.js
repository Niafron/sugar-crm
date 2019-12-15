(function () {

    'use strict';

    const LOCAL_TOKEN_NAME = 'token';
    const CORS_PROXY = 'https://cors-anywhere.herokuapp.com/';
    const BASE_URI = 'https://sg-dev-exercise.demo.sugarcrm.eu/rest/v11';
    const USERNAME = 'admin';
    const PASSWORD = '3j{rK7rb9p';

    const objectToUri = (object) => {
        let str = '';
        for (let key in object) {
            if (str !== '') {
                str += '&';
            } else {
                str += '?';
            }
            if (key === 'no_key') {
                str += object[key];
            } else {
                str += `${key}=${object[key]}`;
            }
        }

        return str;
    };

    const buildRequest = (method, url) => {
        console.warn(`call ${method}`, url);
        const request = new XMLHttpRequest();
        request.open(method, url, true);
        request.setRequestHeader('Access-Control-Allow-Origin', '*');
        request.setRequestHeader("Accept", 'application/json');
        if (localStorage.getItem(LOCAL_TOKEN_NAME)) {
            request.setRequestHeader('Authorization', `Bearer ${localStorage.getItem(LOCAL_TOKEN_NAME)}`);
        }
        request.onerror = (error) => {
            console.error('Error on request', error);
        };

        return request;
    };

    const getNewToken = () => {
        if (!localStorage.getItem(LOCAL_TOKEN_NAME)) {
            const body = `{"grant_type":"password","client_id":"sugar","client_secret":"","username":"${USERNAME}","password":"${PASSWORD}","platform":"base"}`;
            const request = buildRequest('POST', `${CORS_PROXY}${BASE_URI}/oauth2/token`);
            request.onload = () => {
                if (request.status >= 200 && request.status < 400) {
                    localStorage.setItem(LOCAL_TOKEN_NAME, JSON.parse(request.response).access_token);
                    tokenState();
                } else {
                    console.error(`Error on request status (${request.status})`, request.response);
                }
            };

            request.send(body);
        }
    };

    const clearToken = () => {
        localStorage.removeItem(LOCAL_TOKEN_NAME);
        noTokenState();
    };

    const getCases = (evt) => {
        if (evt.currentTarget.contactId) {
            const linkElement = evt.currentTarget;
            linkElement.style.display = 'none';
            const query = objectToUri({
                max_num: 5,
                fields: 'id,name,account_name,account_id'
            });
            const request = buildRequest('GET', `${CORS_PROXY}${BASE_URI}/Contacts/${linkElement.contactId}/link/cases${query}`);
            request.onload = () => {
                if (request.status >= 200 && request.status < 400) {
                    const response = JSON.parse(request.response);
                    if (response.records &&  Array.isArray(response.records)) {
                        response.records.forEach(ticket => {
                            const nodeLi = document.createElement('li');
                            nodeLi.innerHTML = `${ticket.name} - par : <strong>${ticket.account_name}</strong>`;
                            const ulElement = document.getElementById(`cases_${linkElement.contactId}`);
                            ulElement.appendChild(nodeLi);
                        });
                    } else {
                        getContactsElement.disabled = false;
                        console.error('Error on response format', request.response);
                    }
                } else {
                    getContactsElement.disabled = false;
                    console.error(`Error on request status (${request.status})`, request.response);
                }
            };

            request.send();
        }
    };

    const getContacts = () => {
        getContactsElement.disabled = true;
        const query = objectToUri({
            max_num: 20,
            offset: 0,
            fields: 'id,first_name,last_name,email,primary_address_street,primary_address_postalcode,primary_address_city',
            order_by: 'date_entered',
            no_key: 'filter[0][$or][0][first_name][$contains]=a&filter[0][$or][1][last_name][$contains]=b',
        });
        const request = buildRequest('GET', `${CORS_PROXY}${BASE_URI}/Contacts${query}`);
        request.onload = () => {
            if (request.status >= 200 && request.status < 400) {
                const response = JSON.parse(request.response);
                if (response.records &&  Array.isArray(response.records)) {
                    response.records.forEach(contact => {
                        const nodeLi = document.createElement('li');
                        let htmlContent = `<strong>${contact.first_name}</strong> ${contact.last_name} - <em>${contact.email[0].email_address}</em>`;
                        htmlContent += ` | ${contact.primary_address_street} ${contact.primary_address_postalcode} <strong>${contact.primary_address_city}</strong>`;
                        htmlContent += `<br><a id="${contact.id}" href="#" onclick="return false;" style="margin-left: 10px">voir les tickets</a>`;
                        htmlContent += `<br><ul id="cases_${contact.id}" style="margin-left: 10px"></ul>`;
                        nodeLi.innerHTML = htmlContent;
                        contactsListElement.appendChild(nodeLi);
                        const linkElement = document.getElementById(contact.id);
                        linkElement.contactId = contact.id;
                        linkElement.addEventListener('click', getCases);
                    });

                } else {
                    getContactsElement.disabled = false;
                    console.error('Error on response format', request.response);
                }
            } else {
                getContactsElement.disabled = false;
                console.error(`Error on request status (${request.status})`, request.response);
            }
        };

        request.send();
    };

    const tokenState = () => {
        getTokenElement.style.display = 'none';
        clearTokenElement.style.display = 'inline';
        contactsBlockElement.style.display = 'block';
        firstMessageElement.innerHTML = `Vous disposez du token <strong>${localStorage.getItem(LOCAL_TOKEN_NAME)}</strong>.`;
        contactsListElement.innerHTML = '';
        getContactsElement.disabled = false;
    };

    const noTokenState = () => {
        getTokenElement.style.display = 'block';
        clearTokenElement.style.display = 'none';
        contactsBlockElement.style.display = 'none';
        firstMessageElement.innerHTML = 'Vous devez commencer par obtenir un token pour interroger l\'api sugar.';
    };

    const getTokenElement = document.getElementById('get_token');
    const clearTokenElement = document.getElementById('clear_token');
    const firstMessageElement = document.getElementById('first_message');
    const contactsBlockElement = document.getElementById('contacts_block');
    const getContactsElement = document.getElementById('get_contacts');
    const contactsListElement = document.getElementById('contacts_list');
    getTokenElement.addEventListener('click', getNewToken);
    clearTokenElement.addEventListener('click', clearToken);
    getContactsElement.addEventListener('click', getContacts);

    if (localStorage.getItem(LOCAL_TOKEN_NAME)) {
        tokenState();
    } else {
        noTokenState();
    }
})();
