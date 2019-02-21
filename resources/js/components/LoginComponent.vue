<template>
    <div class="wrapper">
        <div class="login-panel navbar-expand-md navbar-light navbar-laravel">
            <div v-if="!isAuth" class="login-panel-buttons">
                <a href="" class="login-button" v-on:click.prevent="showLoginForm">Войти</a>
                <a href="" class="register-button" v-on:click.prevent="showRegisterForm">Регистрация</a>
            </div>
            <div v-else class="logged-user-panel">
                Добро пожаловать, <span class="logged-user-name">{{ authUser.name }}!</span>
                <a href="" class="logout-button" @click.prevent="logout">Выход</a>
            </div>
        </div>


        <div class="row justify-content-center">
            <modal
                    v-show="isModalVisible.loginForm && !isAuth"
                    @close="closeModal('loginForm')"
            >
                <template slot="header">
                    Логин
                </template>
                <template slot="body">
                    <form id="loginForm" @submit.prevent.stop="login">
                        <div class="form-group row">
                            <label for="l-email" class="col-md-4 col-form-label text-md-right">E-Mail</label>

                            <div class="col-md-6">
                                <input id="l-email" type="email" class="form-control" name="email" value="" required autofocus
                                       v-model="loginForm.email">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="l-password" class="col-md-4 col-form-label text-md-right">Пароль</label>

                            <div class="col-md-6">
                                <input id="l-password" type="password" class="form-control" name="password" required
                                       v-model="loginForm.password" @change="hideLoginError" @keypress="hideLoginError">
                            </div>

                            <div class="form-error-small" v-show="loginWrongData">
                                Неверное сочетание логина и пароля
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6 offset-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" v-model="loginForm.remember">

                                    <label class="form-check-label" for="remember">
                                        Запомнить пароль
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" id="loginFormSubmit" class="btn btn-primary">
                                    Войти
                                </button>
                            </div>
                        </div>
                    </form>
                </template>
                <template slot="footer">Что за херня?</template>
            </modal>

            <modal
                    v-show="isModalVisible.registerForm && !isAuth"
                    @close="closeModal('registerForm')"
            >
                <template slot="header">
                    Регистрация
                </template>
                <template slot="body">
                    <form id="registerForm" @submit.prevent.stop="register">
                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">Имя</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control" name="name" value="" required autofocus
                                       v-model="registerForm.name">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="r-email" class="col-md-4 col-form-label text-md-right">E-Mail</label>

                            <div class="col-md-6">
                                <input id="r-email" type="email" class="form-control" name="email" value="" required autofocus
                                       v-model="registerForm.email">
                            </div>

                            <div class="form-error-small" v-show="1 || registerFormErrors.email">
                                {{ registerFormErrors.email }}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="r-password" class="col-md-4 col-form-label text-md-right">Пароль</label>

                            <div class="col-md-6">
                                <input id="r-password" type="password" class="form-control" name="password" required
                                       v-model="registerForm.password" @change="hideLoginError" @keypress="hideLoginError">
                            </div>

                            <div class="form-error-small" v-show="1 || registerFormErrors.password">
                                {{ registerFormErrors.password }}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="confirmPassword" class="col-md-4 col-form-label text-md-right">Пароль ещё раз</label>

                            <div class="col-md-6">
                                <input id="confirmPassword" type="password" class="form-control" name="password_confirmation" required
                                       v-model="registerForm.password_confirmation" @change="hideLoginError" @keypress="hideLoginError">
                            </div>

                            <div class="form-error-small" v-show="registerFormErrors.password_confirmation">
                                {{ registerFormErrors.password_confirmation }}
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" id="registerFormSubmit" class="btn btn-primary">
                                    Войти
                                </button>
                            </div>
                        </div>
                    </form>
                </template>
                <template slot="footer">Что за херня?</template>
            </modal>
        </div>
    </div>

</template>

<script>

    import modal from './Modal.vue';
    /*let loginFormComponent = {
        template: './LoginForm.vue',
        props: ['loginForm']
    }; */
    // Vue.component('login-form', require('./LoginForm.vue').default);


    export default {
        mounted() {
            console.log('Component mounted.');
            this.checkAuth();
        },

        data() {
            return {
                loginForm: {},
                registerForm: {},
                isAuth: false,
                authUser: {},
                isModalVisible: {
                    loginForm: false,
                    registerForm: false
                },
                loginWrongData: false,
                loginFormErrors: {},
                registerFormErrors: {}
            }
        },

        components: {
            modal,
            // 'login-form': loginFormComponent
        },

        methods: {
            checkAuth() {
                axios.get('/my_check_auth').then(response => {
                    this.setUser(response.data.result);
                });
            },
            login() {
                axios.post('/my_login', this.loginForm).then(response => {
                    this.setUser(response.data.result);
                    this.loginWrongData = response.data && response.data.result === false;
                });
            },
            setUser(user) {
                this.isAuth = !!user;
                this.authUser = user ? user : {};
                if (this.isAuth) {
                    this.closeModal('loginForm');
                    this.closeModal('registerForm');
                }
            },
            showLoginForm() {
                this.isModalVisible.registerForm = false;
                this.isModalVisible.loginForm = true;
            },
            showRegisterForm() {
                this.isModalVisible.loginForm = false;
                this.isModalVisible.registerForm = true;
            },
            closeModal(key) {
                this.isModalVisible[key] = false;
            },
            logout() {
                axios.get('/my_logout').then(response => {
                    if (response.data.result) {
                        this.setUser(null);
                    }
                });
            },
            hideLoginError() {
                this.loginWrongData = false;
            },
            register() {
                let that = this;
                let data = this.registerForm;
                this.registerFormErrors = {};
                if (!data.password || data.password.length < 5) {
                    Vue.set(this.registerFormErrors, 'password', 'Пароль должен быть не короче 5 символов');
                }
                if (data.password !== data.password_confirmation) {
                    Vue.set(this.registerFormErrors, 'password_confirmation', 'Пароли не совпадают');
                }
                if (Object.keys(this.registerFormErrors).length <= 0) {
                    axios.post('/my_register', data).catch(error => {
                        if (error.response.data && error.response.data.errors) {
                            // Ошибки валидации
                            let errorsData = error.response.data.errors;
                            for (let key in errorsData) {
                                Vue.set(this.registerFormErrors, key, errorsData[key].join('br/'));
                            }
                        }
                    }).then(response => {
                        console.log('Response');
                        console.log(JSON.stringify(response));
                        if (response && response.data) {
                            this.setUser(response.data.result);
                        }
                    });
                }
            },
        }
    }
</script>
