import React from "react";
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

class App extends React.Component {

    constructor(props) {
        super(props);

        this.state = {
            items: [],
            isDataLoaded: false,
            range: '1month'
        }
    }

    fetchApiData() {
        fetch(rechart.apiUrl + '/rechart/v1/get_data?range=' + this.state.range)
            .then((res) => res.json())
            .then((json) => {
                this.setState({
                    items: json,
                    isDataLoaded: true,
                    range: this.state.range
                })
            });
    }

    componentDidMount() {
        this.fetchApiData();
    }

    handleChange = (e) => {
        this.setState({range: e.target.value}, () => {
            this.fetchApiData();
        });
    }


    render() {
        const { isDataLoaded, items, range } = this.state;

        if (!isDataLoaded) {
            return (
                <div style={{textAlign:'center'}}>
                    <strong>Loading data from API...</strong>
                </div>
            )
        }

        return (
            <div style={{height:'300px'}}>
                <select value={this.state.range} onChange={this.handleChange}>
                    <option value="7days">7 Days</option>
                    <option value="15days">15 Days</option>
                    <option value="1month">1 Month</option>
                </select>
                <ResponsiveContainer width="100%" height="100%">
                    <LineChart
                        width={500}
                        height={300}
                        data={items}
                        margin={{
                            top: 5,
                            right: 30,
                            left: 20,
                            bottom: 5,
                        }}
                    >
                        <CartesianGrid strokeDasharray="3 3" />
                        <XAxis dataKey="date" />
                        <YAxis />
                        <Tooltip />
                        <Legend />
                        <Line type="monotone" dataKey="value" stroke="#8884d8" activeDot={{ r: 8 }} />
                    </LineChart>
                </ResponsiveContainer>
            </div>
        )
    }

}

export default App;
