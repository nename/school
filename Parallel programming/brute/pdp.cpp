#include <iostream>
#include <vector>
#include <algorithm>
#include <chrono>

using namespace std;

typedef unsigned short int un_s;

enum Colour{
    Green,
    Red,
    Uncolored
};

typedef struct{
    un_s first;
    un_s second;
    un_s value;
} Edge;

unsigned long int maxVal = 0, callCount = 0;
vector<pair<vector<Colour>, vector<Edge>>> solutions;

// print function
void print_graphs(){
    for (int i = 0; i < solutions.size(); i++){
        cout << " --- SOLUTION #" << i+1 << " ---" << endl;

        cout << "Verticles" << endl;
        vector<Colour> verticesC = solutions[i].first;

        int counter = 0;
        for (auto a : verticesC){
            cout << "verticle: " << counter << " colour: " << (a == Green ? "Green" : "Red") << endl;
            counter++;
        }

        cout << endl << "Edges" << endl;

        vector<Edge> edgesC = solutions[i].second;

        for (auto a : edgesC){
            cout << "from " << a.first << " to " << a.second << endl;
        }

        cout << endl;
    }
}

// edge compare
bool compare_edges(Edge& a, Edge& b){
    return a.value > b.value;
}

bool is_valid_graph(vector<Colour>& vertices, vector<Edge>& edges){
    for (auto edge : edges){
        Colour first = vertices[edge.first];
        Colour second = vertices[edge.second];

        if (first != Uncolored && first == second) return false;
    }
    return true;
}

int get_value(vector<Edge>& edges){
    un_s res = 0;
    for (auto edge : edges){
        res += edge.value;
    }
    return res;
}

bool check_vectors(vector<Colour>& vertices){
    for (int i = 0; i < solutions.size(); i++){
        vector<Colour> verticesC = solutions[i].first;

        if (verticesC == vertices) return true;
    }
    return false;
}

void solve(vector<Colour> vertices, vector<Edge> remainingEdges, vector<Edge> currentEdges = {}, 
            un_s currVal = 0, un_s remVal = 0){

    // function call count
    callCount++;

    // ending condition no edges
    if (remainingEdges.size() == 0){
        if (currVal > maxVal){
            solutions.clear();
            solutions.push_back(make_pair(vertices, currentEdges));
            maxVal = currVal;
        } else if (currVal == maxVal){
            //if (!check_vectors(vertices))
            solutions.push_back(make_pair(vertices, currentEdges));
        }
        return;
    } 

    // no better sollution
    if ((currVal + remVal) < maxVal) return;

    // new max
    if (currVal > maxVal){
        solutions.clear();
        solutions.push_back(make_pair(vertices, currentEdges));
        maxVal = currVal;
    }

    // remove first remaining
    Edge temp = remainingEdges.front();

    // if the edge is uncolored from both sides - find next edge with at least one colored
    if (vertices[temp.first] == Uncolored && vertices[temp.second] == Uncolored){
        int counter = 0;
        for (auto a : remainingEdges){
            // if (vertices[a.first] != Uncolored || vertices[a.second] != Uncolored){
            if ((vertices[a.first] != Uncolored && vertices[a.second] == Uncolored) || 
            (vertices[a.first] == Uncolored && vertices[a.second] != Uncolored)){
                temp = a;
                remainingEdges.erase(remainingEdges.begin() + counter);
                break;
            }
            counter++;
        }
    } else {
        remainingEdges.erase(remainingEdges.begin());
    }

    // vector<Edge> newCurrent(currentEdges);
    vector<Edge> newCurrent = currentEdges;

    newCurrent.push_back(temp);

    // vector<Colour> newVertices(vertices);
    vector<Colour> newVertices = vertices;

    if (vertices[temp.first] == Uncolored){
        newVertices[temp.first] = vertices[temp.second] == Green ? Red : Green;
    } else if (vertices[temp.second] == Uncolored) {
        newVertices[temp.second] = vertices[temp.first] == Green ? Red : Green;
    } 

    // edge used
    if (is_valid_graph(newVertices, newCurrent))
        solve(newVertices, remainingEdges, newCurrent, currVal + temp.value, remVal - temp.value);

    // edge not used
    solve(vertices, remainingEdges, currentEdges, currVal, remVal - temp.value);
}

int main(){
    vector<Edge> edges;
    un_s graphSize;
    int value = 0;

    // read graph size
    cin >> graphSize;

    // set vertices
    vector<Colour> vertices(graphSize, Uncolored);

    // create edges of vertices with values
    for (int i = 0; i < graphSize; i++){
        for (int j = 0; j < graphSize; j++){
            cin >> value;
            // symetric matrix
            if (value > 0 && j > i){
                Edge temp;
                temp.first = i;
                temp.second = j;
                temp.value = value;

                edges.push_back(temp);
            }
        value = 0;
        }
    }

    // sort
    std::sort(edges.begin(), edges.end(), compare_edges);

    // colour first
    vertices[edges.front().first] = Green;

    // remaining value
    unsigned int remVal = get_value(edges);

    cout << "begin solving" << endl;

    auto start = chrono::high_resolution_clock::now();

    solve(vertices, edges, {}, 0, remVal);

    auto stop = chrono::high_resolution_clock::now();

    print_graphs();

    cout << "maxVal: " << maxVal << " with calls: " << callCount << " solutions: " << solutions.size() << endl;
    cout << "time: " << chrono::duration_cast<chrono::milliseconds>(stop - start).count() << endl;
    return 0;
}