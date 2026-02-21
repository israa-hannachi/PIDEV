from flask import Flask, request, jsonify
from sentence_transformers import SentenceTransformer, util

app = Flask(__name__)
model = SentenceTransformer('paraphrase-multilingual-MiniLM-L12-v2')

@app.route('/compare', methods=['POST'])
def compare():
    data = request.json
    correct = data['correct']
    student = data['student']
    embeddings = model.encode([correct, student])
    similarity = util.cos_sim(embeddings[0], embeddings[1]).item()
    return jsonify({"score": similarity})

if __name__ == '__main__':
    app.run(port=5000)
