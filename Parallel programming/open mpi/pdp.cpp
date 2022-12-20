#include "pdp.hpp"

using namespace std;

// function prints solution
void print_graph(State solution){
    cout << " --- SOLUTION ---" << endl;

    cout << "Verticles" << endl;
    vector<Color> verticesC = solution.vertices;

    int counter = 0;
    for (auto a : verticesC){
        cout << "verticle: " << counter << " Color: " << (a == Green ? "Green" : "Red") << endl;
        counter++;
    }

    cout << endl << "Edges" << endl;

    vector<Edge> edgesC = solution.current;

    for (auto a : edgesC){
        cout << "from " << a.first << " to " << a.second << endl;
    }
}

bool compare_edges(Edge& a, Edge& b){
    return a.value > b.value;
}

bool compare_states(State& a, State& b){
	return a.currVal > b.currVal;
}

// check if its biparite graph
bool is_valid_graph(vector<Color>& vertices, vector<Edge>& edges){
    for (auto edge : edges){
        Color first = vertices[edge.first];
        Color second = vertices[edge.second];

        if (first != Uncolored && first == second) return false;
    }
    return true;
}

// calculates value of graph
int get_value(vector<Edge>& edges){
    un_s res = 0;
    for (auto edge : edges){
        res += edge.value;
    }
    return res;
}

class Solver {
    public:
    unsigned int maxVal = 0;
    vector<State> solutions;

    void solve(State state){
        // new max value
        if (state.currVal >= maxVal){
            #pragma omp critical
            {
                // new max
                if (state.currVal > maxVal){
                    solutions.clear();
                    solutions.push_back(state);
                    maxVal = state.currVal;

                // another solution
                } else if (state.currVal == maxVal){
                    // if (!check_existing_solution(vertices))
                    solutions.push_back(state);
                }
            }
        }

        // ending conditions
        if (state.remaining.size() == 0 || (state.currVal + state.remVal) < maxVal) return;

        // remove first remaining
        Edge temp = state.remaining.front();

        // if the edge is uncolored from both sides - find next edge with at least one side colored
        if (state.vertices[temp.first] == Uncolored && state.vertices[temp.second] == Uncolored){
            int counter = 0;
            for (auto a : state.remaining){
                if ((state.vertices[a.first] != Uncolored && state.vertices[a.second] == Uncolored) || 
                (state.vertices[a.first] == Uncolored && state.vertices[a.second] != Uncolored)){
                    temp = a;
                    state.remaining.erase(state.remaining.begin() + counter);
                    break;
                }
                counter++;
            }
        } else {
            state.remaining.erase(state.remaining.begin());
        }

        // saving used edge
        vector<Edge> newCurrent(state.current);

        newCurrent.push_back(temp);

        // new colored vertices
        vector<Color> newVertices(state.vertices);

        // set colors of edge
        if (state.vertices[temp.first] == Uncolored){
            newVertices[temp.first] = state.vertices[temp.second] == Green ? Red : Green;
        } else if (state.vertices[temp.second] == Uncolored) {
            newVertices[temp.second] = state.vertices[temp.first] == Green ? Red : Green;
        } 

        // edge used
        if (is_valid_graph(newVertices, newCurrent))
            solve(State(newVertices, state.remaining, newCurrent, state.currVal + temp.value, state.remVal - temp.value));

        // edge not used
        solve(State(state.vertices, state.remaining, state.current, state.currVal, state.remVal - temp.value));
    }

    vector<State> expand_states(vector<State> states, unsigned int limit){
        while (states.size() < limit){
            if (states[0].remaining.size() == 0)
                break;

            State tmp = states.front();
            states.erase(states.begin());

            // bad branch
            if ((tmp.currVal + tmp.remVal) < maxVal) continue;

            // new max ?
            if (tmp.currVal > maxVal){
                if (tmp.currVal > maxVal){
                    maxVal = tmp.currVal;
                }
            }

            // get new vals
            vector<Edge> newRemaining(tmp.remaining);
            vector<Edge> newCurrent(tmp.current);
            vector<Color> newVertices(tmp.vertices);

            // take first edge
            Edge tmpEdge = newRemaining.front();
            newRemaining.erase(newRemaining.begin());

            newCurrent.push_back(tmpEdge);

            // edge not colored
            if (tmp.vertices[tmpEdge.first] == Uncolored && tmp.vertices[tmpEdge.second] == Uncolored){
                

                newVertices[tmpEdge.first] = Red;
                newVertices[tmpEdge.second] = Green;

                if (is_valid_graph(newVertices, newCurrent)){
                    states.push_back(State(newVertices, newRemaining, newCurrent, tmp.currVal + tmpEdge.value, tmp.remVal - tmpEdge.value));
                }

                newVertices[tmpEdge.first] = Green;
                newVertices[tmpEdge.second] = Red;

                if (is_valid_graph(newVertices, newCurrent)){
                    states.push_back(State(newVertices, newRemaining, newCurrent, tmp.currVal + tmpEdge.value, tmp.remVal - tmpEdge.value));
                }
            // edge colored from one side    
            } else {
                if (tmp.vertices[tmpEdge.first] == Uncolored){
                    newVertices[tmpEdge.first] = tmp.vertices[tmpEdge.second] == Green ? Red : Green;
                } else {
                    newVertices[tmpEdge.second] = tmp.vertices[tmpEdge.first] == Green ? Red : Green;
                }

                if (is_valid_graph(newVertices, newCurrent)){
                    states.push_back(State(newVertices, newRemaining, newCurrent, tmp.currVal + tmpEdge.value, tmp.remVal - tmpEdge.value));
                }
            }

            // edge not used
            states.push_back(State(tmp.vertices, newRemaining, tmp.current, tmp.currVal, tmp.remVal - tmpEdge.value));
        }
        return states;
    }
};

string serialize(State data){
    std::stringstream ss;
    boost::archive::text_oarchive oa(ss);
    oa << data;
    string serialized = ss.str();
    
    return serialized;
}

State deserialize(string str){
    std::stringstream ss;
    ss << str;
    boost::archive::text_iarchive ia(ss);
    State tmp;
    ia >> tmp;

    return tmp;
}

int main(int argc, char* argv[]){
    std::stringstream ss;
    MPI_Status status;
    int rank, slaves;
    string line;
    ifstream file;

    int threads = atoi(argv[1]);

    // Initialize MPI
    MPI_Init(&argc, &argv);
    MPI_Comm_rank(MPI_COMM_WORLD, &rank);
    MPI_Comm_size(MPI_COMM_WORLD, &slaves);

    if (rank == 0){
        // MASTER
        vector<Edge> edges;
        int graphSize;
        int value = 0;
        int free_slaves = 0;
        unsigned int maxVal = 0;
        vector<State> solutions;

        // read graph size
        file.open(argv[2]);
        getline(file, line);

        graphSize = stoi(line);

        // set vertices
        vector<Color> vertices(graphSize, Uncolored);

        // create edges of vertices with values
        int j = 0;
        string word;
        while (getline(file, line)){
            stringstream sd(line);
            for (int i = 0; i < graphSize; i++){
                sd >> word;
                value = stoi(word);
                if (value > 0 && i > j){
                    Edge temp(i, j, value);

                    edges.push_back(temp);
                }
            }
            j++;
        }

	    file.close();

        std::sort(edges.begin(), edges.end(), compare_edges);

        vertices[edges.front().first] = Green;

        unsigned int remVal = get_value(edges);

        vector<State> states;
        states.push_back(State(vertices, edges, {}, 0, remVal));

        Solver s;
        s.maxVal = maxVal;

        vector<State> expanded = s.expand_states(states, 30);
        
        std::sort(expanded.begin(), expanded.end(), compare_states);

        maxVal = s.maxVal;

        int slave, size;

	    auto start = chrono::high_resolution_clock::now();

        // giving out work
        for (int i = 1; i < slaves; i++){
            #ifdef DEBUG
            cout << "master sending job to slave #" << i << endl << std::flush;
            #endif
            State tmp = expanded.front();
            expanded.erase(expanded.begin());
			
	        tmp.maxVal = maxVal; 
            
   	        string serialized = serialize(tmp);

            size = serialized.size() * sizeof(char);

            MPI_Send(serialized.c_str(), size, MPI_CHAR, i, TAG_JOB, MPI_COMM_WORLD);
        }

        char* buffer = NULL;

        while (1){
            if (expanded.size() == 0 && free_slaves == slaves - 1)
                break;

            MPI_Probe(MPI_ANY_SOURCE, MPI_ANY_TAG, MPI_COMM_WORLD, &status);

            // get result from slave
            if (status.MPI_TAG == TAG_RES){
                // recv solution
                MPI_Get_count(&status, MPI_CHAR, &size);

                #ifdef DEBUG
                cout << "master receiving res from " << status.MPI_SOURCE << endl << std::flush;
                #endif

                buffer = (char*)malloc((unsigned long)size);
                MPI_Recv(buffer, size, MPI_CHAR, status.MPI_SOURCE, TAG_RES, MPI_COMM_WORLD, &status);

                State tmp(deserialize(buffer));

                // better solution
                if (tmp.currVal > maxVal){
                    solutions.clear();
                    solutions.push_back(tmp);
                    maxVal = tmp.currVal;
                } else if (tmp.currVal == maxVal){
                    solutions.push_back(tmp);
                }

                // send another job
                if (expanded.size() != 0){
                    State tmp = expanded.front();

                    expanded.erase(expanded.begin());

		            tmp.maxVal = maxVal;

	                string serialized = serialize(tmp);

                    size = serialized.size() * sizeof(char);

                    #ifdef DEBUG
                    cout << "master sending job to " << status.MPI_SOURCE << endl << std::flush;
                    #endif

                    MPI_Send(serialized.c_str(), size, MPI_CHAR, status.MPI_SOURCE, TAG_JOB, MPI_COMM_WORLD);
                } else {
                    #ifdef DEBUG
                    cout << "master sending end to " << status.MPI_SOURCE << endl << std::flush;
                    #endif

                    char end = 1;
                    MPI_Send(&end, 1, MPI_CHAR, status.MPI_SOURCE, TAG_END, MPI_COMM_WORLD);
                    free_slaves++;
                }
            }
        }
	    auto stop = chrono::high_resolution_clock::now();
        print_graph(solutions[0]);
        cout << endl << "MaxVal: " << maxVal << endl;
	    cout << "time: " << chrono::duration_cast<chrono::milliseconds>(stop - start).count() << " ms" << endl;
    } else {
        // SLAVE

        int size = 0;
        char* buffer = NULL;
		int maxVal = 0;
        
        while (1){
			// receive data
            MPI_Probe(0, MPI_ANY_TAG, MPI_COMM_WORLD, &status);

			// end condition
            if (status.MPI_TAG == TAG_END){
                #ifdef DEBUG
                cout << "slave " << rank << "  terminating" << endl << std::flush;
                #endif
                break;
			// another work
            } else {
                #ifdef DEBUG
                cout << "slave " << rank << " receiving data" << endl << std::flush;
                #endif

                MPI_Get_count(&status, MPI_CHAR, &size);
                buffer = (char*)malloc(size);
                MPI_Recv(buffer, size, MPI_CHAR, 0, TAG_JOB, MPI_COMM_WORLD, &status);

                vector<State> tmp;
                tmp.push_back(State(deserialize(buffer)));

                Solver s;
                s.maxVal = tmp[0].maxVal;

                vector<State> expanded = s.expand_states(tmp, 5 * threads);

                #ifdef DEBUG
                cout << "slave " << rank << " working" << endl << std::flush;
                #endif

                #pragma omp parallel for schedule(dynamic) num_threads(threads)
                for (int i = 0; i < expanded.size(); i++){
		            State tmp(expanded[i]);
                    s.solve(tmp);
                }

                string serialized = serialize(s.solutions[0]);

                size = serialized.size() * sizeof(char);

                #ifdef DEBUG
                cout << "slave " << rank << " sending res" << endl << std::flush;
                #endif

                MPI_Send(serialized.c_str(), size, MPI_CHAR, 0, TAG_RES, MPI_COMM_WORLD);
            }
        }
    }
    MPI_Finalize();

    return 0;
}
