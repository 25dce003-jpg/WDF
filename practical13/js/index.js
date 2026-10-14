// USERNAME

let userID = localStorage.getItem("userID");

if (userID) {
    document.getElementById("userID").textContent = userID;
} else {
    document.getElementById("userID").textContent = "Student";
}


// 2. HAMBURGER MENU


const menuBtn = document.getElementById("menuBtn");
const navbar = document.getElementById("navbar");

menuBtn.addEventListener("click", function () {

    navbar.classList.toggle("active");

});


 // ==========================================
// 3. NOTIFICATION
// ==========================================

const notification = document.getElementById("notification");
const closeNotification = document.getElementById("closeNotification");

closeNotification.addEventListener("click", function () {

    notification.style.display = "none";

});


// ==========================================
// 4. DARK / LIGHT MODE
// ==========================================

const themeBtn = document.getElementById("themeBtn");


// Check saved theme when page loads

let savedTheme = localStorage.getItem("theme");

if (savedTheme === "dark") {

    document.body.classList.add("dark");
    themeBtn.textContent = "☀️ Light Mode";

}


// Change theme

themeBtn.addEventListener("click", function () {

    document.body.classList.toggle("dark");

    if (document.body.classList.contains("dark")) {

        themeBtn.textContent = "☀️ Light Mode";
        localStorage.setItem("theme", "dark");

    } else {

        themeBtn.textContent = "🌙 Dark Mode";
        localStorage.setItem("theme", "light");

    }

});


// ==========================================
// 5. MODAL POPUP
// ==========================================

const modal = document.getElementById("modal");
const openModal = document.getElementById("openModal");
const closeModal = document.getElementById("closeModal");


// Open modal

openModal.addEventListener("click", function () {

    modal.style.display = "flex";

});


// Close modal

closeModal.addEventListener("click", function () {

    modal.style.display = "none";

});


// Close modal when clicking outside

window.addEventListener("click", function (event) {

    if (event.target === modal) {

        modal.style.display = "none";

    }

});


// ==========================================
// 6. CONTENT SLIDER
// ==========================================

const slides = [

    {
        title: "Java Exam",
        text: "Java Programming examination will be conducted on Monday."
    },

    {
        title: "Network Assignment",
        text: "Submit your Computer Network assignment before Friday."
    },

    {
        title: "WDF Practical",
        text: "Web Development Framework practical submission is next week."
    },

    {
        title: "Semester Result",
        text: "Semester results will be available on the Student HUB Portal."
    }

];


let currentSlide = 0;

const slideTitle = document.getElementById("slideTitle");
const slideText = document.getElementById("slideText");


// Display slide

function showSlide() {

    slideTitle.textContent = slides[currentSlide].title;

    slideText.textContent = slides[currentSlide].text;

}


// Next button

document.getElementById("nextBtn").addEventListener("click", function () {

    currentSlide++;

    if (currentSlide >= slides.length) {

        currentSlide = 0;

    }

    showSlide();

});


// Previous button

document.getElementById("prevBtn").addEventListener("click", function () {

    currentSlide--;

    if (currentSlide < 0) {

        currentSlide = slides.length - 1;

    }

    showSlide();

});


// ==========================================
// 7. COLLAPSIBLE FAQ
// ==========================================

const faqQuestions = document.querySelectorAll(".faq-question");

faqQuestions.forEach(function (question) {

    question.addEventListener("click", function () {

        const answer = question.nextElementSibling;

        // Select the PLUS/MINUS span only
        const icon = question.querySelector("span:last-child");


        if (answer.style.maxHeight) {

            // Close FAQ
            answer.style.maxHeight = null;

            // Change minus back to plus
            icon.textContent = "+";

        } else {

            // Open FAQ
            answer.style.maxHeight = answer.scrollHeight + "px";

            // Change plus to minus
            icon.textContent = "−";

        }

    });

});