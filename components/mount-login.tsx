import React from 'react';
import ReactDOM from 'react-dom/client';
import { LoginForm } from './ui/auth-form';
import '../index.css';

const el = document.getElementById('react-login-form');
if (el) {
  const actionUrl = el.dataset.action || 'login.php';
  const errorMessage = el.dataset.error || '';
  ReactDOM.createRoot(el).render(
    <React.StrictMode>
      <LoginForm actionUrl={actionUrl} errorMessage={errorMessage} />
    </React.StrictMode>
  );
}
