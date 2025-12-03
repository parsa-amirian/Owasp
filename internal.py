from __future__ import annotations

import os
from contextlib import closing

import pymysql
import pymysql.cursors
from flask import Flask, jsonify, make_response

app = Flask(__name__)


def get_db_connection():
    return pymysql.connect(
        host=os.getenv("WORLD_DB_HOST", "10.0.0.10"),
        user=os.getenv("WORLD_DB_USER", "world"),
        password=os.getenv("WORLD_DB_PASSWORD", "1234"),
        database=os.getenv("WORLD_DB_NAME", "world"),
        cursorclass=pymysql.cursors.DictCursor,
    )


def serialize_user(row: dict) -> dict:
    """Convert DB rows to JSON-serializable dicts."""
    serialized = {}
    for key, value in row.items():
        if isinstance(value, (bytes, bytearray)):
            serialized[key] = value.decode("utf-8")
        elif hasattr(value, "isoformat"):
            serialized[key] = value.isoformat()
        else:
            serialized[key] = value
    return serialized


@app.get("/api/user/<int:user_id>")
def get_user(user_id: int):
    try:
        with closing(get_db_connection()) as conn:
            with closing(conn.cursor()) as cursor:
                cursor.execute("SELECT * FROM users WHERE id = %s", (user_id,))
                user = cursor.fetchone()

        if not user:
            return make_response(jsonify({"error": "User not found"}), 404)

        return jsonify(serialize_user(user))
    except pymysql.Error as exc:
        app.logger.exception("Database error while fetching user %s", user_id)
        return make_response(jsonify({"error": "Internal server error"}), 500)


if __name__ == "__main__":
    app.run(host="0.0.0.0", port=int(os.getenv("PORT", 5000)), debug=False)