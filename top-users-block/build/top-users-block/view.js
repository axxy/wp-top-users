/******/ (() => { // webpackBootstrap
/*!*************************************!*\
  !*** ./src/top-users-block/view.js ***!
  \*************************************/
document.addEventListener("DOMContentLoaded", function () {
  const topUsersBlocks = document.querySelectorAll(".wp-block-ahmedyahya-top-users-block");
  topUsersBlocks.forEach(async block => {
    const container = block.querySelector(".top-users-block-container");
    const order = container.dataset.order || "desc";
    const numberOfUsers = parseInt(container.dataset.numberOfUsers) || 5;
    const title = document.createElement("h2");
    title.textContent = `Top ${numberOfUsers} Users`;
    container.appendChild(title);
    try {
      const response = await fetch(`/wp-json/ahmedyahya/v1/top-users?limit=${numberOfUsers}`);
      let users = await response.json();
      users.sort((a, b) => {
        return order === "asc" ? a.total_order_value - b.total_order_value : b.total_order_value - a.total_order_value;
      });
      const usersList = document.createElement("ul");
      usersList.className = "top-users-list";
      users.forEach(user => {
        const listItem = document.createElement("li");
        listItem.innerHTML = `<strong>${user.name}</strong> Total orders amount: ${user.total_order_value}`;
        usersList.appendChild(listItem);
      });
      container.innerHTML = `<h2>Top ${numberOfUsers} Users</h2>`;
      container.appendChild(usersList);
    } catch (error) {
      console.error("Error fetching top users:", error);
      container.textContent = "Error loading top users.";
    }
  });
});
/******/ })()
;
//# sourceMappingURL=view.js.map