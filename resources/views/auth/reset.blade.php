<div class="auth-container">
    <h2>Reset Password</h2>
    <form @submit.prevent="resetPassword">
      <div class="input-group">
        <label for="email">Email</label>
        <input id="email" v-model="email" type="email" required />
      </div>
      <div class="input-group">
        <label for="token">Reset Token</label>
        <input id="token" v-model="token" type="text" required />
      </div>
      <div class="input-group">
        <label for="password">New Password</label>
        <input id="password" v-model="password" type="password" required />
      </div>
      <div class="input-group">
        <label for="password_confirmation">Confirm New Password</label>
        <input id="password_confirmation" v-model="password_confirmation" type="password" required />
      </div>
      <button type="submit">Reset Password</button>
    </form>
  </div>
  
  <style>
  body {
    margin: 0;
    padding: 0;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #f7f7f7;
  }
  
  .auth-container {
    width: 100%;
    max-width: 400px;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 8px;
    background-color: white;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin-top: 50px;
  }
  
  h2 {
    text-align: center;
    margin-bottom: 20px;
  }
  
  .input-group {
    margin-bottom: 15px;
  }
  
  label {
    display: block;
    margin-bottom: 5px;
  }
  
  input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
  }
  
  button {
    width: 100%;
    padding: 10px;
    background-color: #17a2b8;
    color: #fff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
  }
  
  button:hover {
    background-color: #138496;
  }
  </style>
  