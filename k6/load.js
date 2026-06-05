import { login, runScenario } from './common.js';

const loadVus = Number(__ENV.K6_LOAD_VUS || 10);
const rampUp = __ENV.K6_LOAD_RAMP_UP || '1m';
const hold = __ENV.K6_LOAD_HOLD || '5m';
const rampDown = __ENV.K6_LOAD_RAMP_DOWN || '30s';
const pauseSeconds = Number(__ENV.K6_PAUSE_SECONDS || 0.5);

export const options = {
    stages: [
        { duration: rampUp, target: loadVus },
        { duration: hold, target: loadVus },
        { duration: rampDown, target: 0 },
    ],
    thresholds: {
        http_req_failed: ['rate<0.05'],
        checks: ['rate>0.95'],
        http_req_duration: ['p(95)<800'],
    },
};

export function setup() {
    return login();
}

export default function (data) {
    runScenario(data, pauseSeconds);
}
