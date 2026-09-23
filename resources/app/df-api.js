/**
 * DoceFlow — cliente API (dados na nuvem, sem backup JSON).
 */
(function (global) {
  const TOKEN_KEY = 'df_access_token';
  let serverSnapshot = null;
  let persistTimer = null;

  function apiPath(path) {
    let base = location.pathname
      .replace(/\/index\.php(\/.*)?$/i, '')
      .replace(/\/app\/?$/, '')
      .replace(/\/+$/, '');
    return (base || '') + '/index.php' + path;
  }

  function authHeaders() {
    const t = localStorage.getItem(TOKEN_KEY);
    const h = { Accept: 'application/json', 'Content-Type': 'application/json' };
    if (t) h.Authorization = 'Bearer ' + t;
    return h;
  }

  async function request(method, path, body) {
    const res = await fetch(apiPath(path), {
      method,
      headers: authHeaders(),
      body: body !== undefined ? JSON.stringify(body) : undefined,
    });
    const raw = await res.text();
    let data = {};
    try {
      data = raw ? JSON.parse(raw) : {};
    } catch (e) {
      throw new Error('Resposta inválida do servidor.');
    }
    if (!res.ok) {
      throw new Error(data.erro || data.message || res.statusText);
    }
    return data;
  }

  function clone(o) {
    return JSON.parse(JSON.stringify(o));
  }

  async function syncCollection(basePath, items, snapshotItems) {
    const snap = snapshotItems || [];
    const snapIds = new Set(snap.map((i) => i.id));
    const stateIds = new Set(items.map((i) => i.id));

    for (const s of snap) {
      if (!stateIds.has(s.id)) {
        await request('DELETE', basePath + '/' + encodeURIComponent(s.id));
      }
    }
    for (const item of items) {
      if (!snapIds.has(item.id)) {
        await request('POST', basePath, item);
      } else {
        const prev = snap.find((x) => x.id === item.id);
        if (JSON.stringify(prev) !== JSON.stringify(item)) {
          await request('PUT', basePath + '/' + encodeURIComponent(item.id), item);
        }
      }
    }
  }

  const api = {
    useApi() {
      return !!localStorage.getItem(TOKEN_KEY) || global.DF_FORCE_API === true;
    },

    async loadStateFresh() {
      const [clientes, produtos, encomendas, estoque, settings, meta] = await Promise.all([
        request('GET', '/api/v1/clientes'),
        request('GET', '/api/v1/produtos'),
        request('GET', '/api/v1/encomendas'),
        request('GET', '/api/v1/estoque'),
        request('GET', '/api/v1/settings'),
        request('GET', '/api/v1/meta'),
      ]);
      const loaded = {
        clientes: clientes.data || [],
        produtos: produtos.data || [],
        encomendas: encomendas.data || [],
        estoque: estoque.data || [],
        theme: (settings.data && settings.data.theme) || 'rosa',
        hoje: meta.hoje || new Date().toISOString().slice(0, 10),
      };
      serverSnapshot = {
        clientes: clone(loaded.clientes),
        produtos: clone(loaded.produtos),
        encomendas: clone(loaded.encomendas),
        estoque: clone(loaded.estoque),
        theme: loaded.theme,
      };
      return loaded;
    },

    async loadState() {
      return api.loadStateFresh();
    },

    async persistState(state) {
      if (!serverSnapshot) {
        await api.loadStateFresh();
      }
      await syncCollection('/api/v1/clientes', state.clientes, serverSnapshot.clientes);
      await syncCollection('/api/v1/produtos', state.produtos, serverSnapshot.produtos);
      await syncCollection('/api/v1/encomendas', state.encomendas, serverSnapshot.encomendas);
      await syncCollection('/api/v1/estoque', state.estoque, serverSnapshot.estoque);
      if (state.theme !== serverSnapshot.theme) {
        await request('PATCH', '/api/v1/settings', { theme: state.theme });
      }
      serverSnapshot = {
        clientes: clone(state.clientes),
        produtos: clone(state.produtos),
        encomendas: clone(state.encomendas),
        estoque: clone(state.estoque),
        theme: state.theme,
      };
    },

  };

  global.scheduleDoceFlowSync = function (state) {
    if (!api.useApi()) return;
    clearTimeout(persistTimer);
    persistTimer = setTimeout(function () {
      api.persistState(state).catch(function (e) {
        console.error('[DoceFlow] falha ao salvar', e);
        if (typeof dfAlert === 'function') {
          dfAlert('Erro ao salvar', e.message || String(e), 'error');
        }
      });
    }, 600);
  };

  global.DoceFlowApi = api;
})(window);
