import { login, runScenario } from './common.js';

const maxVus = Number(__ENV.K6_STRESS_MAX_VUS || 50);
const pauseSeconds = Number(__ENV.K6_PAUSE_SECONDS || 0.3);

export const options = {
    stages: [
        { duration: '1m', target: Math.min(10, maxVus) },
        { duration: '2m', target: Math.min(25, maxVus) },
        { duration: '2m', target: Math.min(40, maxVus) },
        { duration: '2m', target: maxVus },
        { duration: '2m', target: maxVus },
        { duration: '2m', target: 0 },
    ],
    thresholds: {
        http_req_failed: ['rate<0.10'],
        checks: ['rate>0.90'],
        http_req_duration: ['p(95)<2000'],
    },
};

export function setup() {
    return login();
}

export default function (data) {
    runScenario(data, pauseSeconds);
}
