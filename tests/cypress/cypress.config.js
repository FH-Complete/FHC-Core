const { defineConfig } = require("cypress");
require("dotenv").config();

module.exports = defineConfig({
  allowCypressEnv: true,
   
  e2e: {
    baseUrl: process.env.BASE_URL || "http://localhost:8080",
    defaultCommandTimeout: 20000,
    pageLoadTimeout: 10000,
    retries: {
      runMode: 1,
      openMode: 1
    }
  },
  env: {
    adminusername: process.env.USER_NAME || "2",
    adminpassword: process.env.USER_PASSWORD || "2",
    loginAsUser: process.env.LOGIN_AS_USER || process.env.USER_NAME || "demoadmin"
  }

});
