<div class="auth-container">
    <h2>Register</h2>
    <form @submit.prevent="register">
      <div class="input-group">
        <label for="name">Name</label>
        <input id="name" v-model="name" type="text" required />
      </div>
      <div class="input-group">
        <label for="email">Email</label>
        <input id="email" v-model="email" type="email" required />
      </div>
      <div class="input-group">
        <label for="password">Password</label>
        <input id="password" v-model="password" type="password" required />
      </div>
      <div class="input-group">
        <label for="password_confirmation">Confirm Password</label>
        <input id="password_confirmation" v-model="password_confirmation" type="password" required />
      </div>
      <button type="submit">Register</button>
      <p><a href="/login">Already have an account? Login</a></p>
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
    background-color: #28a745;
    color: #fff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
  }
  
  button:hover {
    background-color: #218838;
  }
  
  p {
    text-align: center;
    margin-top: 10px;
  }
  
  a {
    color: #28a745;
    text-decoration: none;
  }
  
  a:hover {
    text-decoration: underline;
  }
  </style>
  