# About

This server is useful for making simple env tests. It stores all request from past.

It can catch all method of requests (GET, POST, PUT, DELETE) on all endpoints 
(exclude _GET /history_ and _DELETE /history_ - explanation is below).

## How to run?
```bash
docker run -it -p="80:80" ss:FakeHttpServer
```

## Endpoints

**PUT /configure-endpoint** - it sets (or overrides) body which will be returned
Body:
```json
{
  "endpoint": "/abc/def",
  "method": "POST",
  "returnHttpCode": 200,
  "returnBody": "..."
}
```

**DELETE /configure-endpoint** 

It deletes all configured endpoint


**GET /history**

Returns JSON with all historical requests to the server. For example:
```json
[
  {
    "method": "GET",
    "endpoint": "/users",
    "headers": [],
    "body": {}
  },
  {
    "method": "POST",
    "endpoint": "/login",
    "headers": [],
    "body": "Lorem ipsum dolorem sit ament..."
  }
]
```

**DELETE /history**

Delete all historical requests to the server.
