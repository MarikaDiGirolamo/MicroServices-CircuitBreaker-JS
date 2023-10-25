class Service {
    doSomething() {
        const randomNumber = Math.floor(Math.random() * 10);
        const isError = randomNumber > 2;
        if (isError) {
            throw new Error("Service unavailable");
        }
        console.log("Service invoked!");
    }
}

class Client {
    constructor(service) {
        this.service = service;
    }

    invokeService() {
        this.service.doSomething();
    }
}

// MAIN

const client = new Client(new Service());
client.invokeService();