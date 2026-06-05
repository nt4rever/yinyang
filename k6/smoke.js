import { login, runScenario } from './common.js';

export const options = {
    vus: 1,
    duration: '45s',
    thresholds: {
        http_req_failed: ['rate<0.01'],
        checks: ['rate>0.99'],
    },
};

export function setup() {
    return login();
}

export default function (data) {
    runScenario(data, 1);
}
