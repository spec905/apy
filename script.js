//base elements
const addItemsButton = document.getElementById('add-items-button');
const closeBtn = document.getElementById('close-btn');
const itemslisting = document.getElementById('items-listing');
const itemForm = document.getElementById('item_form');
const listingGrid = document.getElementById('listings_grid');

//filter elements
const searchForItem = document.getElementById('search-for-item');
const itemsType = document.getElementById('items-type');
const locationFilter = document.getElementById('location-filter');
const maxPriceFilter = document.getElementById('max-price-filter');

let listings = (typeof phpItems !== 'undefined') ? phpItems : [];

/* like / unlike un item  */
function toggleLike(button) {
    const itemId = button.dataset.id;

    const formData = new FormData();
    formData.append('action', 'toggle_like');
    formData.append('item_id', itemId);

    fetch('save_item.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.classList.toggle('liked', data.liked);
            button.querySelector('.like-count').textContent = data.like_count;
            button.firstChild.textContent = data.liked ? '❤️ ' : '🤍 ';
        } else {
            alert(data.message || 'Error updating like');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Something went wrong');
    });
}

/* comments */
function toggleComments(button) {
    const card = button.closest('.item-card');
    const commentsSection = card.querySelector('.comments-section');
    commentsSection.style.display = commentsSection.style.display === 'block' ? 'none' : 'block';
}

function submitComment(form) {
    const itemId = form.dataset.id;
    const input = form.querySelector('.comment-input');
    const text = input.value.trim();
    if (text === '') return;

    const formData = new FormData();
    formData.append('action', 'add_comment');
    formData.append('item_id', itemId);
    formData.append('comment_text', text);

    fetch('save_item.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const commentsList = form.closest('.comments-section').querySelector('.comments-list');
            if (commentsList.querySelector('.no-comments')) commentsList.innerHTML = '';
            commentsList.insertAdjacentHTML('beforeend', `
                <div class="comment">
                    <strong>${data.comment.name}</strong>
                    <p>${data.comment.comment_text}</p>
                </div>
            `);
            input.value = '';
        } else {
            alert(data.message || 'Error posting comment');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Something went wrong');
    });
}

/* cart */
function addToCart(button) {
    const itemId = button.dataset.id;

    const formData = new FormData();
    formData.append('action', 'add_to_cart');
    formData.append('item_id', itemId);

    fetch('save_item.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.textContent = 'Added';
            button.disabled = true;
        } else {
            alert(data.message || 'Error adding to cart');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Something went wrong');
    });
}

/* retirer un item du panier */
const cartList = document.querySelector('.cart-list');
if (cartList) {
    cartList.addEventListener('click', (event) => {
        const removeBtn = event.target.closest('.remove-from-cart-btn');
        if (!removeBtn) return;

        const itemId = removeBtn.dataset.id;

        const formData = new FormData();
        formData.append('action', 'remove_from_cart');
        formData.append('item_id', itemId);

        fetch('save_item.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error removing item');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Something went wrong');
        });
    });
}


function attachCardListeners(grid) {
    grid.addEventListener('click', (event) => {
        const likeBtn = event.target.closest('.like-btn');
        if (likeBtn) toggleLike(likeBtn);

        const commentsBtn = event.target.closest('.comments-toggle-btn');
        if (commentsBtn) toggleComments(commentsBtn);

        const cartBtn = event.target.closest('.add-to-cart-btn');
        if (cartBtn) addToCart(cartBtn);
    });

    grid.addEventListener('submit', (event) => {
        const form = event.target.closest('.comment-form');
        if (form) {
            event.preventDefault();
            submitComment(form);
        }
    });
}

if (addItemsButton) {

    addItemsButton.addEventListener('click', () => {
        itemslisting.style.display = 'flex';
    });

    closeBtn.addEventListener('click', () => {
        itemslisting.style.display = 'none';
    });

    itemslisting.addEventListener('click', (event) => {
        if (event.target === itemslisting) {
            itemslisting.style.display = 'none';
        }
    });

    function renderListing(itemsToShow = listings) {
        listingGrid.innerHTML = '';
        itemsToShow.forEach(item => {
            const card = document.createElement('div');
            card.classList.add('item-card');

            card.innerHTML = `
                <img src="${item.image}" alt="${item.title}">
                <div class="item-card-info">
                     <h3>${item.title}</h3>
                     <p>Category: ${item.category}</p>
                     <p>Location: ${item.location}</p>
                     <p>Price: $${item.price}</p>
                     <p>description: ${item.description}</p>
                     <button class="like-btn ${item.liked_by_user > 0 ? 'liked' : ''}" data-id="${item.id}">
                         ${item.liked_by_user > 0 ? '❤️' : '🤍'} <span class="like-count">${item.like_count}</span>
                     </button>
                     <button class="comments-toggle-btn" data-id="${item.id}">💬 Comments</button>
                     ${typeof phpUser !== 'undefined' && phpUser && item.posted_by !== phpUser.email
                         ? `<button class="add-to-cart-btn" data-id="${item.id}">🛒 Add to cart</button>`
                         : ''}
                     <div class="comments-section" style="display:none;">
                         <div class="comments-list">
                             ${item.comments.length === 0
                                 ? '<p class="no-comments">No comments yet.</p>'
                                 : item.comments.map(c => `<div class="comment"><strong>${c.name}</strong><p>${c.comment_text}</p></div>`).join('')}
                         </div>
                         <form class="comment-form" data-id="${item.id}">
                             <input type="text" class="comment-input" placeholder="Write a comment...">
                             <button type="submit">Post</button>
                         </form>
                     </div>
                </div>
            `;
            listingGrid.appendChild(card);
        });
    }

    attachCardListeners(listingGrid);

    itemForm.addEventListener('submit', (e) => {
        e.preventDefault();

        const formData = new FormData();
        formData.append('title', document.getElementById('item_title').value);
        formData.append('price', document.getElementById('item_price').value);
        formData.append('category', document.getElementById('item_category').value);
        formData.append('location', document.getElementById('item_location').value);
        formData.append('image', document.getElementById('item_image').files[0]);
        formData.append('description', document.getElementById('item_description').value);

        fetch('save_item.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Item posted successfully!');
                itemForm.reset();
                itemslisting.style.display = 'none';
                location.reload();
            } else {
                alert(data.message || 'Error posting item');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Something went wrong');
        });
    });

    function applyFilter() {
        const searchValue = searchForItem.value.toLowerCase();
        const categoryValue = itemsType.value;
        const locationValue = locationFilter.value.toLowerCase();
        const maxPriceValue = maxPriceFilter.value;

        const filtered = listings.filter(item => {
            const matchSearch = item.title.toLowerCase().includes(searchValue);
            const matchcategory = categoryValue === '' || item.category === categoryValue;
            const matchlocation = locationValue === '' || item.location.toLowerCase().includes(locationValue);
            const matchPrice = maxPriceValue === '' || item.price <= Number(maxPriceValue);
            return matchSearch && matchcategory && matchlocation && matchPrice;
        });

        renderListing(filtered);
    }

    searchForItem.addEventListener('input', applyFilter);
    itemsType.addEventListener('change', applyFilter);
    locationFilter.addEventListener('input', applyFilter);
    maxPriceFilter.addEventListener('input', applyFilter);

    renderListing();
}

/* register-login */
function showForm(formId) {
    document.querySelectorAll(".acc-box").forEach(box => box.classList.remove("active"));
    document.getElementById(formId).classList.add("active");
}

/* profile */
const profilepic = document.getElementById('profile_pic');
const uploadbtn = document.getElementById('upload-btn');
const picinput = document.getElementById('profile_picture_input');
const submitbtn = document.getElementById('submit-upload-btn');

if (profilepic) {
    const User = phpUser;

    if (User) {
        if (User.profile_picture) {
            profilepic.innerHTML = `<img src="${User.profile_picture}" alt="profile" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`;
        } else {
            profilepic.textContent = User.name.charAt(0).toUpperCase();
        }
        document.getElementById('user_name').textContent = User.name;
        document.getElementById('user_email').textContent = User.email;
        document.getElementById('user_location').textContent = User.country;
    }

    /* items added by user */
    const profilegrid = document.getElementById('profile-items-grid');
    const myitems = listings.filter(item => item.posted_by === User?.email);
    myitems.forEach(item => {
        const card = document.createElement('div');
        card.classList.add('item-card');
        card.innerHTML = `
        <img src="${item.image}" alt="${item.title}">
        <div class="item-card-info">
            <h3>${item.title}</h3>
            <p>Category: ${item.category}</p>
            <p>Price: $${item.price}</p>
            <button class="like-btn ${item.liked_by_user > 0 ? 'liked' : ''}" data-id="${item.id}">
                ${item.liked_by_user > 0 ? '❤️' : '🤍'} <span class="like-count">${item.like_count}</span>
            </button>
            <button class="comments-toggle-btn" data-id="${item.id}"> Comments</button>
            <div class="comments-section" style="display:none;">
                <div class="comments-list">
                    ${item.comments.length === 0
                        ? '<p class="no-comments">No comments yet.</p>'
                        : item.comments.map(c => `<div class="comment"><strong>${c.name}</strong><p>${c.comment_text}</p></div>`).join('')}
                </div>
                <form class="comment-form" data-id="${item.id}">
                    <input type="text" class="comment-input" placeholder="Write a comment...">
                    <button type="submit">Post</button>
                </form>
            </div>
            <button class="delete-item-btn" data-id="${item.id}">Delete</button>
        </div>
        `;
        profilegrid.appendChild(card);
    });

    attachCardListeners(profilegrid);

    /* delete an item */
    profilegrid.addEventListener('click', (event) => {
        if (!event.target.classList.contains('delete-item-btn')) return;

        const itemId = event.target.dataset.id;
        if (!confirm('Delete this item?')) return;

        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', itemId);

        fetch('save_item.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                event.target.closest('.item-card').remove();
            } else {
                alert(data.message || 'Error deleting item');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Something went wrong');
        });
    });

    /* preview + upload of the profile picture */
    if (uploadbtn && picinput) {
        uploadbtn.addEventListener('click', () => {
            picinput.click();
        });

        picinput.addEventListener('change', () => {
            if (picinput.files.length > 0) {
                uploadbtn.style.display = 'none';
                submitbtn.style.display = 'inline-block';
                profilepic.innerHTML = `<img src="${URL.createObjectURL(picinput.files[0])}" alt="preview" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`;
            }
        });
    }
}