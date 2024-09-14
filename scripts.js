document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded');
    const loginModal = document.getElementById('loginModal');
    console.log('Login Modal initial display:', loginModal.style.display);

    loginModal.style.display = 'none';
    console.log('Login Modal display after setting to none:', loginModal.style.display);

    const searchBar = document.querySelector('.search-bar input');
    const searchButton = document.querySelector('.search-bar button');
    const productsContainer = document.getElementById('products');
    const categoryLinks = document.querySelectorAll('#categoryList a');
    const loginButton = document.getElementById('loginButton');
    const logoutButton = document.getElementById('logoutButton');
    const welcomeMessage = document.getElementById('welcomeMessage');
    const loginSubmit = document.getElementById('loginSubmit');
    const goldAmount = document.getElementById('goldAmount');
    const balance = document.getElementById('balance');
    const characterSelect = document.getElementById('characterSelect');

    // 检查localStorage中的登录信息
    const savedUsername = localStorage.getItem('username');
    const savedGold = localStorage.getItem('gold');
    const savedCharacters = JSON.parse(localStorage.getItem('characters'));
    if (savedUsername && savedGold && savedCharacters) {
        welcomeMessage.textContent = `欢迎你 ${savedUsername}`;
        welcomeMessage.style.display = 'inline';
        loginButton.style.display = 'none';
        logoutButton.style.display = 'inline';
        goldAmount.textContent = savedGold;
        balance.style.display = 'block';
        populateCharacterSelect(savedCharacters);
    } else {
        welcomeMessage.style.display = 'none';
        loginButton.style.display = 'inline';
        logoutButton.style.display = 'none';
        balance.style.display = 'none';
    }

    // 绑定分类链接的事件监听器
    bindCategoryLinks();

    loginButton.addEventListener('click', function() {
        loginModal.style.display = 'flex';
    });

    // 添加关闭登录模态框的功能
    const closeModal = document.createElement('span');
    closeModal.textContent = '×';
    closeModal.style.position = 'absolute';
    closeModal.style.right = '20px';
    closeModal.style.top = '10px';
    closeModal.style.fontSize = '24px';
    closeModal.style.cursor = 'pointer';
    loginModal.appendChild(closeModal);

    closeModal.addEventListener('click', function() {
        loginModal.style.display = 'none';
    });

    // 点击模态框外部关闭模态框
    window.addEventListener('click', function(event) {
        if (event.target == loginModal) {
            loginModal.style.display = 'none';
        }
    });

    loginSubmit.addEventListener('click', function() {
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        fetch('login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ username, password }),
            credentials: 'include' // 确保会话信息被传递
        })
        .then(response => response.json())
        .then(data => {
            console.log('Login response:', data); // 添加日志
            if (data.success) {
                loginModal.style.display = 'none';
                welcomeMessage.textContent = `欢迎你 ${username}`;
                welcomeMessage.style.display = 'inline';
                loginButton.style.display = 'none';
                logoutButton.style.display = 'inline';
                goldAmount.textContent = data.gold;
                balance.style.display = 'block';
                populateCharacterSelect(data.characters);
                // 保存登录信息到localStorage
                localStorage.setItem('username', username);
                localStorage.setItem('gold', data.gold);
                localStorage.setItem('characters', JSON.stringify(data.characters));
            } else {
                alert('登录失败：' + (data.message || '请检查账号和密码'));
            }
        })
        .catch(error => {
            console.error('Login error:', error); // 添加错误日志
            alert('登录过程中发生错误，请稍后再试');
        });
    });

    logoutButton.addEventListener('click', function() {
        const confirmation = confirm('你确定要注销账号吗？');
        if (confirmation) {
            fetch('logout.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        welcomeMessage.style.display = 'none';
                        loginButton.style.display = 'inline';
                        logoutButton.style.display = 'none';
                        goldAmount.textContent = 'xxx';
                        balance.style.display = 'none';
                        characterSelect.innerHTML = '<option value="" disabled selected>请选择你的角色</option>';
                        // 清除localStorage中的登录信息
                        localStorage.removeItem('username');
                        localStorage.removeItem('gold');
                        localStorage.removeItem('characters');
                    }
                });
        }
    });

    const buyModal = document.getElementById('buyModal');
    const buyMessage = document.getElementById('buyMessage');
    const confirmBuy = document.getElementById('confirmBuy');
    const cancelBuy = document.getElementById('cancelBuy');
    const resultModal = document.getElementById('resultModal');
    const resultTitle = document.getElementById('resultTitle');
    const resultMessage = document.getElementById('resultMessage');
    const closeResult = document.getElementById('closeResult');
    const characterSelectModal = document.getElementById('characterSelectModal');
    const closeCharacterSelect = document.getElementById('closeCharacterSelect');
    let currentProduct = null;

    // 绑定购买按钮的事件监听器
    document.querySelectorAll('.buy-button').forEach(button => {
        button.addEventListener('click', function() {
            const productElement = this.closest('.product');
            const productName = productElement.querySelector('.product-name').textContent;
            const productPrice = productElement.querySelector('.price').textContent.match(/\d+/)[0];
            const itemID = productElement.dataset.itemid;
            const quantity = productElement.dataset.quantity; // 获取商品数量
            const characterName = document.getElementById('characterSelect').value;

            if (!characterName) {
                characterSelectMessage.textContent = `请先选择一个角色才能购买 ${productName}。`;
                characterSelectModal.style.display = 'flex';
                return;
            }

            buyMessage.innerHTML = `你是否花费<span style="color: red;">${productPrice}金</span>购买<br>购买角色为：<span style="color: red;">${characterName}</span>`;
            buyModal.style.display = 'flex';
            currentProduct = { itemID, productPrice, characterName, quantity, itemName: productName }; // 添加 itemName
        });
    });

    confirmBuy.addEventListener('click', function() {
        if (currentProduct) {
            fetch('buy.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(currentProduct),
                credentials: 'same-origin'  // 确保发送凭证
            })
            .then(response => response.json())
            .then(data => {
                let message = data.message;
                if (data.soapResponses) {
                    message += '\n\nSOAP 响应:\n' + data.soapResponses.join('\n');
                }
                if (data.debug) {
                    message += '\n\n调试信息:\n' + JSON.stringify(data.debug, null, 2);
                }
                
                resultTitle.textContent = data.success ? '购买成功' : '购买失败';
                resultMessage.textContent = message;
                resultModal.style.display = 'flex';
                
                if (data.success) {
                    goldAmount.textContent = data.newGold;
                    localStorage.setItem('gold', data.newGold);  // 更新本地存储的金币数量
                }
                buyModal.style.display = 'none';
                currentProduct = null;
            })
            .catch(error => {
                console.error('购买过程中发生错误:', error);
                resultTitle.textContent = '购买失败';
                resultMessage.textContent = '购买过程中发生错误，请稍后再试';
                resultModal.style.display = 'flex';
                buyModal.style.display = 'none';
                currentProduct = null;
            });
        }
    });

    cancelBuy.addEventListener('click', function() {
        buyModal.style.display = 'none';
        currentProduct = null;
    });

    closeResult.addEventListener('click', function() {
        resultModal.style.display = 'none';
    });

    closeCharacterSelect.addEventListener('click', function() {
        characterSelectModal.style.display = 'none';
    });

    // 添加新的模态框事件监听器
    closeCharacterSelect.addEventListener('click', function() {
        characterSelectModal.style.display = 'none';
    });

    // ... 现有代码 ...

    searchButton.addEventListener('click', function() {
        const searchTerm = searchBar.value.toLowerCase();
        const products = document.querySelectorAll('.product');
        products.forEach(product => {
            const productName = product.querySelector('.product-name').textContent.toLowerCase();
            const productDescription = product.querySelector('.product-description').textContent.toLowerCase();
            if (productName.includes(searchTerm) || productDescription.includes(searchTerm)) {
                product.style.display = 'block';
            } else {
                product.style.display = 'none';
            }
        });
    });

    // ... 现有代码 ...
});

function bindCategoryLinks() {
    const categoryLinks = document.querySelectorAll('#categoryList a');
    categoryLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const category = this.getAttribute('href').split('=')[1];
            filterProducts(category);
            setActiveLink(this);
        });
    });
}

function filterProducts(category) {
    const products = document.querySelectorAll('.product');
    products.forEach(product => {
        if (category === 'all' || product.dataset.category === category) {
            product.style.display = 'block';
        } else {
            product.style.display = 'none';
        }
    });
}

function setActiveLink(activeLink) {
    const categoryLinks = document.querySelectorAll('#categoryList a');
    categoryLinks.forEach(link => link.classList.remove('active'));
    activeLink.classList.add('active');
}

function populateCharacterSelect(characters) {
    const characterSelect = document.getElementById('characterSelect');
    characterSelect.innerHTML = '<option value="" disabled selected>请选择你的角色</option>';
    characters.forEach(character => {
        const option = document.createElement('option');
        option.value = character;
        option.textContent = character;
        characterSelect.appendChild(option);
    });
}