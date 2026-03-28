const API = 'https://api.saliksic.com/api';

function getToken() { return sessionStorage.getItem('auth_token') || ''; }
function setToken(t) { sessionStorage.setItem('auth_token', t); }
function clearToken() { sessionStorage.removeItem('auth_token'); }

async function apiFetch(path, opts = {}) {
  const headers = Object.assign({ 'Content-Type': 'application/json' }, opts.headers || {});
  const token = getToken();
  if (token) headers['Authorization'] = `Bearer ${token}`;
  const res = await fetch(`${API}${path}`, Object.assign({}, opts, { headers }));
  if (res.status === 401 || res.status === 403) {
    // Redirect to login
    if (!location.pathname.endsWith('/ebm/admin/login.html')) {
      location.href = '/ebm/admin/login.html';
    }
    throw new Error(`Auth error ${res.status}`);
  }
  const json = await res.json().catch(() => ({}));
  return { ok: res.ok, status: res.status, json };
}

function requireAuth() {
  if (!getToken()) {
    location.href = '/ebm/admin/login.html';
  }
}
