import http from 'k6/http';
import { check, sleep } from 'k6';

export const baseUrl = __ENV.BASE_URL || 'http://localhost:8000';
export const email = __ENV.K6_EMAIL || 'test@yinyang.io';
export const password = __ENV.K6_PASSWORD || 'password';

export const jsonHeaders = {
    'Content-Type': 'application/json',
    Accept: 'application/json',
};

export function login() {
    const res = http.post(
        `${baseUrl}/api/v1/login`,
        JSON.stringify({ email, password }),
        { headers: jsonHeaders },
    );

    check(res, { 'login status 200': (r) => r.status === 200 });

    if (res.status !== 200) {
        throw new Error(`login failed with status ${res.status}: ${res.body}`);
    }

    return { token: res.json('access_token') };
}

export function runScenario(data, pauseSeconds = 1) {
    const authHeaders = {
        ...jsonHeaders,
        Authorization: `Bearer ${data.token}`,
    };

    check(http.get(`${baseUrl}/health`), {
        'health is 200': (r) => r.status === 200,
    });

    check(http.get(`${baseUrl}/`), {
        'home is 200': (r) => r.status === 200,
    });

    const profile = http.get(`${baseUrl}/api/v1/profile`, {
        headers: authHeaders,
    });

    check(profile, {
        'profile is 200': (r) => r.status === 200,
        'profile has data': (r) => r.json('data') !== undefined,
    });

    sleep(pauseSeconds);
}
